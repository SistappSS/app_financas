<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, SubscriptionService $subscriptionService)
    {
        $user = $request->user();
        $state = $subscriptionService->syncUserAccess($user);

        return view('app.users.user_index', [
            'subscription' => $state['subscription'],
            'subscriptionHasAccess' => $state['has_access'],
            'subscriptionIsTrial' => $state['is_trial'],
            'subscriptionIsSubscriber' => $state['is_subscriber'],
            'subscriptionIsRenewalAlert' => $state['is_renewal_alert'],
            'subscriptionTrialEndsAt' => $state['trial_ends_at'],
            'subscriptionSubscriberUntil' => $state['subscriber_until'],
            'subscriptionGraceUntil' => $state['grace_until'],
        ]);
    }
}
