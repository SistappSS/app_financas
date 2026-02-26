<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse;

use App\Models\Auth\User;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $guard;
    protected $user;

    public function __construct(StatefulGuard $guard, User $user)
    {
        $this->guard = $guard;
        $this->user = $user;
    }

    public function login(LoginUserRequest $request)
    {
        $request->validated();

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');


        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->canAuthenticate()) {
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'));
            }

            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return back()->with('error', 'Acesso negado.');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas estão incorretas.',
        ]);
    }

    public function register(RegisterUserRequest $request)
    {
        $request->validated();

        $user = $this->user->create([
            'name' => ucwords($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => 1,
            'trial_ends_at' => Carbon::now()->addDays((int) config('asaas.plan.trial_days', 14)),
            'created_at' => Carbon::now()
        ])->assignRole('user');

        Auth::login($user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): LogoutResponse
    {
        $this->guard->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return app(LogoutResponse::class);
    }

    public function welcome(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return $this->authPageResponse($request, 'auth.login');
    }

    public function registerView(Request $request): Response
    {
        return $this->authPageResponse($request, 'auth.register');
    }

    private function authPageResponse(Request $request, string $view): Response
    {
        if ($request->hasSession()) {
            $request->session()->regenerateToken();
        }

        return response()
            ->view($view)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
