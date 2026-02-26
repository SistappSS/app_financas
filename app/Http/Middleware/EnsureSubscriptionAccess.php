<?php

namespace App\Http\Middleware;

use App\Services\Billing\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionAccess
{
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $this->subscriptionService->syncUserAccess($user);

        if ($user->hasRole('admin') || !$user->hasRole('user')) {
            return $next($request);
        }

        $allowedRoutes = [
            'user-view.index',
            'users.index',
            'users.update',
            'billing.subscription.summary',
            'billing.subscription.document',
            'billing.subscription.checkout-pix',
            'logout',
        ];

        $routeName = (string) optional($request->route())->getName();

        if (in_array($routeName, $allowedRoutes, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Assine o plano para acessar este recurso.',
            ], 403);
        }

        return redirect()->route('user-view.index')
            ->with('error', 'Seu período de acesso terminou. Renove para continuar usando o app completo.');
    }
}
