<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\Billing\AsaasService;
use App\Services\Billing\SubscriptionService;
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
        $user = $request->user();
        $subscription = $this->subscriptionService->bootstrapSubscription($user);

        $latestPayment = SubscriptionPayment::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'plan_name' => $subscription->plan_name,
            'amount' => $subscription->amount,
            'status' => $subscription->status,
            'trial_ends_at' => optional($subscription->trial_ends_at)?->toDateTimeString(),
            'current_period_ends_at' => optional($subscription->current_period_ends_at)?->toDateTimeString(),
            'is_trial' => $this->subscriptionService->isTrial($user),
            'has_access' => $this->subscriptionService->isActive($user),
            'cpf_cnpj' => $user->cpf_cnpj,
            'latest_payment' => $latestPayment,
        ]);
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

        $subscription = $this->subscriptionService->bootstrapSubscription($user);

        $customerId = $this->asaasService->ensureCustomer($user);
        $user->forceFill(['asaas_customer_id' => $customerId])->save();

        $pending = SubscriptionPayment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'status' => 'pending',
            'amount' => $subscription->amount,
            'due_date' => now()->addDays((int) config('asaas.plan.grace_days_for_pix', 2))->toDateString(),
        ]);

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
}
