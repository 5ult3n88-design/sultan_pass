<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Notification;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended($this->redirectPath(Auth::user()));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function showRegisterForm(): View
    {
        $languages = Language::query()->select('id', 'name')->orderBy('name')->get();

        return view('auth.register', compact('languages'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'full_name' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'language_pref' => ['nullable', 'exists:languages,id'],
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'language_pref' => $validated['language_pref'] ?? null,
            'role' => 'participant',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended($this->redirectPath($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower($validated['email']);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['email' => __('We could not find a user with that email address.')]);
        }

        if ($user->role === 'admin') {
            return back()->with('status', __('Administrators may update their password directly from the database or their user profile.'));
        }

        $existingPendingRequest = PasswordResetRequest::pending()
            ->where('user_id', $user->id)
            ->first();

        if ($existingPendingRequest) {
            return back()->with('status', __('A password reset request is already awaiting approval. Please contact your administrator for updates.'));
        }

        PasswordResetRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => (string) Str::uuid(),
        ]);

        $message = __('Password reset requested by :name (:email).', [
            'name' => $user->full_name ?: $user->username,
            'email' => $user->email,
        ]);

        User::query()
            ->where('role', 'admin')
            ->where('id', '!=', $user->id)
            ->pluck('id')
            ->each(function ($adminId) use ($message) {
                Notification::create([
                    'user_id' => $adminId,
                    'message' => $message,
                    'notification_type' => 'password_reset',
                    'sent_at' => now(),
                ]);
            });

        return back()->with('status', __('Your request has been sent to the super administrator. You will receive a new password once it is approved.'));
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    protected function redirectPath(User $user): string
    {
        return match ($user->role) {
            'admin' => route('dashboard.admin'),
            'manager' => route('dashboard.manager'),
            'assessor' => route('dashboard.assessor'),
            default => route('dashboard.participant'),
        };
    }
}
