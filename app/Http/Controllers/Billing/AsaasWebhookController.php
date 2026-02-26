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
        $tokenHeader = $request->header('asaas-access-token');
        $expected = config('asaas.sandbox')
            ? config('asaas.webhook_token_sandbox')
            : config('asaas.webhook_token_production');

        abort_unless($expected && hash_equals($expected, (string) $tokenHeader), 401);

        $event = (string) $request->input('event');
        $paymentId = (string) $request->input('payment.id');

        if (!$paymentId) {
            return response()->json(['ok' => true]);
        }

        $payment = SubscriptionPayment::where('asaas_payment_id', $paymentId)->first();

        if (!$payment) {
            return response()->json(['ok' => true]);
        }

        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'], true)) {
            $this->subscriptionService->markPaymentReceived($payment);
        }

        if (in_array($event, ['PAYMENT_OVERDUE', 'PAYMENT_DELETED', 'PAYMENT_REFUNDED'], true)) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['ok' => true]);
    }
}
