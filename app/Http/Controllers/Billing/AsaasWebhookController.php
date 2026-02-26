<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;

class AsaasWebhookController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
    }

    public function __invoke(Request $request)
    {
        $tokenHeader = $request->header('asaas-access-token')
            ?? $request->header('asaas_access_token')
            ?? $request->header('Asaas-Access-Token');

        $expected = config('asaas.sandbox')
            ? config('asaas.webhook_token_sandbox')
            : config('asaas.webhook_token_production');

        if (!empty($expected) && !hash_equals((string) $expected, (string) $tokenHeader)) {
            return response()->json(['ok' => false, 'message' => 'Invalid webhook token'], 401);
        }

        $event = (string) $request->input('event');
        $paymentId = (string) $request->input('payment.id');
        $externalReference = (string) $request->input('payment.externalReference');

        if (!$paymentId && !$externalReference) {
            return response()->json(['ok' => true]);
        }

        $payment = null;
        if ($paymentId) {
            $payment = SubscriptionPayment::where('asaas_payment_id', $paymentId)->first();
        }

        if (!$payment && $externalReference) {
            $payment = SubscriptionPayment::where('id', $externalReference)->first();
        }

        if (!$payment) {
            return response()->json(['ok' => true]);
        }

        if ($payment->status === 'received') {
            return response()->json(['ok' => true]);
        }

        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'], true)) {
            $this->subscriptionService->markPaymentReceived($payment);
        }

        if (in_array($event, ['PAYMENT_OVERDUE', 'PAYMENT_DELETED', 'PAYMENT_REFUNDED'], true)) {
            $payment->update(['status' => 'failed']);
            $this->subscriptionService->syncUserAccess($payment->user);
        }

        return response()->json(['ok' => true]);
    }
}
