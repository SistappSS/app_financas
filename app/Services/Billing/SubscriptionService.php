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

    public function getState(User $user): array
    {
        $subscription = $this->bootstrapSubscription($user);

        $trialEndsAt = $subscription->trial_ends_at;
        $subscriberUntil = $subscription->current_period_ends_at;

        $isTrial = $trialEndsAt && $trialEndsAt->isFuture();

        $graceDays = (int) config('asaas.plan.access_grace_days_after_due', 2);
        $renewalAlertDays = (int) config('asaas.plan.renewal_alert_days', 3);

        $graceUntil = $subscriberUntil?->copy()->addDays($graceDays);
        $isSubscriber = $subscriberUntil && $graceUntil && now()->lte($graceUntil);

        $isRenewalAlert = $subscriberUntil
            && now()->lte($subscriberUntil)
            && now()->diffInDays($subscriberUntil, false) <= $renewalAlertDays;

        $hasAccess = $user->hasRole('admin') || $isTrial || $isSubscriber;

        return [
            'subscription' => $subscription,
            'is_trial' => (bool) $isTrial,
            'is_subscriber' => (bool) $isSubscriber,
            'has_access' => (bool) $hasAccess,
            'is_renewal_alert' => (bool) $isRenewalAlert,
            'trial_ends_at' => $trialEndsAt,
            'subscriber_until' => $subscriberUntil,
            'grace_until' => $graceUntil,
        ];
    }

    public function canGenerateChargeNow(User $user): array
    {
        $state = $this->syncUserAccess($user);
        $windowDays = (int) config('asaas.plan.renewal_alert_days', 3);

        if ($state['is_trial'] && $state['trial_ends_at']) {
            $daysToTrialEnd = now()->diffInDays($state['trial_ends_at'], false);
            if ($daysToTrialEnd > $windowDays) {
                return [false, 'Pagamento liberado apenas nos últimos 3 dias do período grátis.'];
            }
        }

        if ($state['is_subscriber'] && $state['subscriber_until']) {
            $daysToExpire = now()->diffInDays($state['subscriber_until'], false);
            if ($daysToExpire > $windowDays) {
                return [false, 'Assinante até '.$state['subscriber_until']->format('d/m/Y H:i').'. O pagamento só é liberado nos 3 dias finais.'];
            }
        }

        return [true, null];
    }

    public function syncUserAccess(User $user): array
    {
        $state = $this->getState($user);
        $subscription = $state['subscription'];

        if (!$user->hasRole('admin') && !$user->hasRole('additional_user')) {
            $role = 'user';

            if ($state['is_trial']) {
                $role = 'trials';
            } elseif ($state['is_subscriber']) {
                $role = 'subscript';
            }

            if (!$user->hasRole($role)) {
                $user->syncRoles([$role]);
            }
        }

        $status = 'inactive';
        if ($state['is_trial']) {
            $status = 'trialing';
        } elseif ($state['is_subscriber']) {
            $status = 'active';
        }

        $subscription->update([
            'status' => $status,
            'trial_ends_at' => $state['trial_ends_at'],
            'current_period_ends_at' => $state['subscriber_until'],
        ]);

        return $state;
    }

    public function isActive(User $user): bool
    {
        return $this->syncUserAccess($user)['has_access'];
    }

    public function isTrial(User $user): bool
    {
        return $this->syncUserAccess($user)['is_trial'];
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
        $payment->loadMissing('user', 'subscription');

        if ($payment->status === 'received') {
            return;
        }

        $subscription = $payment->subscription ?: $this->bootstrapSubscription($payment->user);

        $endsAt = Carbon::now()->addDays((int) config('asaas.plan.billing_cycle_days', 30));

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
        $this->syncUserAccess($payment->user->fresh());
    }
}
