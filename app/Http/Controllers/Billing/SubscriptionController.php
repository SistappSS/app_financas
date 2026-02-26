<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\Billing\AsaasService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly AsaasService $asaasService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function summary(Request $request)
    {
        return response()->json($this->buildSummary($request->user()));
    }

    public function stream(Request $request)
    {
        $user = $request->user();

        return response()->stream(function () use ($user) {
            for ($i = 0; $i < 12; $i++) {
                $summary = $this->buildSummary($user->fresh());
                echo "event: subscription\n";
                echo 'data: '.json_encode($summary)."\n\n";
                @ob_flush();
                @flush();
                sleep(5);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    private function buildSummary($user): array
    {
        $state = $this->subscriptionService->syncUserAccess($user);
        $subscription = $state['subscription'];

        $latestPayment = SubscriptionPayment::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if ($latestPayment && $latestPayment->status === 'pending' && $latestPayment->asaas_payment_id) {
            $this->syncPaymentFromGateway($latestPayment);
            $latestPayment = $latestPayment->fresh();
            $state = $this->subscriptionService->syncUserAccess($user->fresh());
        }

        $pendingPayment = $latestPayment && $latestPayment->status === 'pending' ? $latestPayment : null;
        [$canCheckout, $checkoutBlockReason] = $this->subscriptionService->canGenerateChargeNow($user->fresh());

        if ($pendingPayment) {
            $canCheckout = true;
            $checkoutBlockReason = null;
        }

        return [
            'plan_name' => $subscription->plan_name,
            'amount' => $subscription->amount,
            'status' => $subscription->status,
            'trial_ends_at' => optional($state['trial_ends_at'])?->toDateTimeString(),
            'subscriber_until' => optional($state['subscriber_until'])?->toDateTimeString(),
            'grace_until' => optional($state['grace_until'])?->toDateTimeString(),
            'is_trial' => $state['is_trial'],
            'is_subscriber' => $state['is_subscriber'],
            'has_access' => $state['has_access'],
            'is_renewal_alert' => $state['is_renewal_alert'],
            'cpf_cnpj' => $user->cpf_cnpj,
            'latest_payment' => $latestPayment,
            'pending_payment' => $pendingPayment,
            'can_checkout' => $canCheckout,
            'checkout_block_reason' => $checkoutBlockReason,
        ];
    }

    public function updateDocument(Request $request)
    {
        $data = $request->validate([
            'cpf_cnpj' => ['required', 'string', 'max:18'],
        ]);

        $cleanDoc = preg_replace('/\D+/', '', $data['cpf_cnpj']);

        if (!in_array(strlen((string) $cleanDoc), [11, 14], true)) {
            return response()->json([
                'message' => 'Informe um CPF/CNPJ válido.',
            ], 422);
        }

        $user = $request->user();
        $user->forceFill(['cpf_cnpj' => $data['cpf_cnpj']])->save();

        if ($user->asaas_customer_id) {
            $this->asaasService->updateCustomerDocument($user);
        }

        return response()->json(['cpf_cnpj' => $user->cpf_cnpj]);
    }

    public function checkoutPix(Request $request)
    {
        $user = $request->user();

        if (!$user->cpf_cnpj) {
            return response()->json([
                'message' => 'Cadastre CPF/CNPJ antes de gerar a cobrança.',
            ], 422);
        }

        [$allowedNow, $reason] = $this->subscriptionService->canGenerateChargeNow($user);
        if (!$allowedNow) {
            return response()->json(['message' => $reason], 422);
        }

        $created = DB::transaction(function () use ($user) {
            $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $subscription = $this->subscriptionService->bootstrapSubscription($lockedUser);

            $openPending = SubscriptionPayment::query()
                ->where('user_id', $lockedUser->id)
                ->where('status', 'pending')
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($openPending) {
                return ['existing_pending' => $openPending, 'subscription' => $subscription, 'user' => $lockedUser];
            }

            $pending = SubscriptionPayment::create([
                'user_id' => $lockedUser->id,
                'subscription_id' => $subscription->id,
                'status' => 'pending',
                'amount' => $subscription->amount,
                'due_date' => now()->addDays((int) config('asaas.plan.grace_days_for_pix', 2))->toDateString(),
            ]);

            return ['pending' => $pending, 'subscription' => $subscription, 'user' => $lockedUser];
        });

        if (!empty($created['existing_pending'])) {
            $openPending = $created['existing_pending'];

            if ($openPending->asaas_payment_id) {
                $this->syncPaymentFromGateway($openPending);
                $openPending = $openPending->fresh();
            }

            if ($openPending && $openPending->status === 'pending') {
                return response()->json([
                    'message' => 'Já existe um PIX pendente para esta assinatura.',
                    'payment' => $openPending,
                ], 409);
            }

            $created = DB::transaction(function () use ($user) {
                $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                $subscription = $this->subscriptionService->bootstrapSubscription($lockedUser);

                $pending = SubscriptionPayment::create([
                    'user_id' => $lockedUser->id,
                    'subscription_id' => $subscription->id,
                    'status' => 'pending',
                    'amount' => $subscription->amount,
                    'due_date' => now()->addDays((int) config('asaas.plan.grace_days_for_pix', 2))->toDateString(),
                ]);

                return ['pending' => $pending, 'subscription' => $subscription, 'user' => $lockedUser];
            });
        }

        $customerId = $this->asaasService->ensureCustomer($created['user']);
        $created['user']->forceFill(['asaas_customer_id' => $customerId])->save();

        $pending = $created['pending'];
        $subscription = $created['subscription'];

        $asaas = $this->asaasService->createPixPayment($customerId, [
            'amount' => $subscription->amount,
            'due_date' => $pending->due_date->toDateString(),
            'description' => 'Assinatura '.$subscription->plan_name,
            'external_reference' => $pending->id,
        ]);

        $payment = $asaas['payment'];
        $pix = $asaas['pix'];

        $pending->update([
            'asaas_payment_id' => $payment['id'] ?? null,
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'pix_qr_code' => $pix['encodedImage'] ?? null,
            'pix_copy_paste' => $pix['payload'] ?? null,
            'payload' => $payment,
        ]);

        return response()->json($pending->fresh(), 201);
    }

    private function syncPaymentFromGateway(SubscriptionPayment $payment): void
    {
        try {
            $gatewayPayment = $this->asaasService->getPayment((string) $payment->asaas_payment_id);
        } catch (\Throwable $exception) {
            return;
        }

        $gatewayStatus = strtoupper((string) ($gatewayPayment['status'] ?? ''));

        if (in_array($gatewayStatus, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'], true)) {
            $this->subscriptionService->markPaymentReceived($payment);
            return;
        }

        if (in_array($gatewayStatus, ['OVERDUE', 'DELETED', 'REFUNDED'], true)) {
            $payment->update(['status' => 'failed', 'payload' => $gatewayPayment]);
            return;
        }

        $payment->update(['status' => 'pending', 'payload' => $gatewayPayment]);
    }
}
