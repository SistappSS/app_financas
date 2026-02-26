<?php

namespace Tests\Feature\Billing;

use App\Models\Auth\User;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\Billing\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionBillingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user');
        Role::findOrCreate('trials');
        Role::findOrCreate('subscript');

        config()->set('asaas.webhook_token_sandbox', 'token-test');
        config()->set('asaas.sandbox', true);
        config()->set('asaas.plan.billing_cycle_days', 30);
        config()->set('asaas.plan.renewal_alert_days', 3);
    }

    public function test_webhook_marks_received_using_payment_status_even_with_unexpected_event(): void
    {
        $user = User::factory()->create();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_name' => 'Plano Premium',
            'amount' => 29.90,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'asaas_payment_id' => 'pay_123',
            'status' => 'pending',
            'amount' => 29.90,
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $response = $this->postJson('/webhooks/asaas', [
            'event' => 'PAYMENT_UPDATED',
            'payment' => [
                'id' => 'pay_123',
                'status' => 'RECEIVED',
                'externalReference' => $payment->id,
            ],
        ], [
            'asaas-access-token' => 'token-test',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('received', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertNotNull($subscription->fresh()->current_period_ends_at);
    }

    public function test_mark_payment_received_extends_from_current_period_instead_of_resetting_cycle(): void
    {
        Carbon::setTestNow('2026-03-20 10:00:00');

        $user = User::factory()->create();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_name' => 'Plano Premium',
            'amount' => 29.90,
            'status' => 'active',
            'current_period_ends_at' => Carbon::parse('2026-03-30 10:00:00'),
        ]);

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'status' => 'pending',
            'amount' => 29.90,
        ]);

        /** @var SubscriptionService $service */
        $service = app(SubscriptionService::class);
        $service->markPaymentReceived($payment);

        $this->assertSame('received', $payment->fresh()->status);
        $this->assertTrue(
            $subscription->fresh()->current_period_ends_at->equalTo(Carbon::parse('2026-04-29 10:00:00'))
        );

        Carbon::setTestNow();
    }
}
