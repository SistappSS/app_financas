<?php

namespace App\Services\Billing;

use App\Models\Auth\User;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function bootstrapSubscription(User $user): Subscription
    {
        $trialEndsAt = $user->trial_ends_at ?? now()->addDays((int) config('asaas.plan.trial_days', 14));

        if (!$user->trial_ends_at) {
            $user->forceFill(['trial_ends_at' => $trialEndsAt])->save();
        }

        return Subscription::firstOrCreate(
            ['user_id' => $user->id],
            [
                'plan_name' => config('asaas.plan.name'),
                'amount' => config('asaas.plan.amount'),
                'status' => 'trialing',
                'trial_ends_at' => $trialEndsAt,
            ]
        );
    }

    public function isActive(User $user): bool
    {
        $subscription = $this->bootstrapSubscription($user);
        $now = now();

        if ($subscription->current_period_ends_at && $subscription->current_period_ends_at->isFuture()) {
            return true;
        }

        return $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture();
    }

    public function isTrial(User $user): bool
    {
        $subscription = $this->bootstrapSubscription($user);

        return $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture();
    }

    public function enforceCreateLimit(User $user, string $key, string $modelClass): void
    {
        if ($this->isActive($user)) {
            return;
        }

        $limit = (int) config("asaas.limits.{$key}", 0);

        if ($limit < 1) {
            throw ValidationException::withMessages([
                'subscription' => 'Seu período grátis acabou. Assine para continuar cadastrando novos registros.',
            ]);
        }

        $count = $modelClass::query()->count();

        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'subscription' => "Você atingiu o limite gratuito de {$limit} registros para {$key}. Assine para liberar mais.",
            ]);
        }
    }

    public function applyReadLimit(User $user, Builder $query, string $key): Builder
    {
        if ($this->isActive($user)) {
            return $query;
        }

        $limit = (int) config("asaas.read_limits.{$key}", 0);

        return $limit > 0 ? $query->latest()->limit($limit) : $query;
    }

    public function markPaymentReceived(SubscriptionPayment $payment): void
    {
        $subscription = $payment->subscription ?: $this->bootstrapSubscription($payment->user);

        $baseDate = $subscription->current_period_ends_at && $subscription->current_period_ends_at->isFuture()
            ? $subscription->current_period_ends_at
            : Carbon::now();

        $endsAt = $baseDate->copy()->addMonth();

        $subscription->update([
            'status' => 'active',
            'current_period_ends_at' => $endsAt,
            'meta' => ['last_payment_id' => $payment->id],
        ]);

        $payment->update([
            'status' => 'received',
            'paid_at' => now(),
        ]);

        $payment->user->forceFill(['subscription_expires_at' => $endsAt])->save();
    }
}
