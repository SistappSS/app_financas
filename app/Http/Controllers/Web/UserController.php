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
        $subscription = $subscriptionService->bootstrapSubscription($user);

        return view('app.users.user_index', [
            'subscription' => $subscription,
            'subscriptionHasAccess' => $subscriptionService->isActive($user),
            'subscriptionIsTrial' => $subscriptionService->isTrial($user),
        ]);
    }
}
