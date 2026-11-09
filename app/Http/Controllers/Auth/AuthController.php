<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the Warm Espresso portal sign-in page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt with 3-strike 15-minute lockout security.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:STAFF,HRD,MANAGER'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email format.',
            'password.required' => 'Password is required.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // 1. User does not exist
        if (! $user) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors([
                    'email' => 'These credentials do not match our registered records.',
                ]);
        }

        // 2. Account lockout check
        if ($user->isLocked()) {
            $secondsLeft = now()->diffInSeconds($user->locked_until, false);
            $minutesLeft = max(1, (int) ceil($secondsLeft / 60));

            Log::warning("Blocked login attempt on locked account [{$user->email}]. Remaining: {$minutesLeft} minutes.");

            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors([
                    'email' => "Your account is currently locked for security reasons due to 3 consecutive failed login attempts. Please retry in {$minutesLeft} minute(s).",
                ]);
        }

        // Reset expired lockout if timestamp is past
        if ($user->locked_until !== null && $user->locked_until->isPast()) {
            $user->update([
                'locked_until' => null,
                'failed_logins' => 0,
            ]);
        }

        // 3. Password Verification
        if (! Hash::check($validated['password'], $user->password_hash)) {
            $user->increment('failed_logins');

            if ($user->failed_logins >= 3) {
                $user->update([
                    'locked_until' => now()->addMinutes(15),
                    'failed_logins' => 3,
                ]);

                Log::alert("Security Lockout Triggered: Account [{$user->email}] locked for 15 minutes due to 3 consecutive failed login attempts.");

                return back()
                    ->withInput($request->only('email', 'role'))
                    ->withErrors([
                        'email' => 'Your account has been automatically locked for 15 minutes due to 3 consecutive failed login attempts.',
                    ]);
            }

            $remainingAttempts = 3 - $user->failed_logins;

            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors([
                    'email' => "Incorrect password. {$remainingAttempts} attempt(s) remaining before automatic 15-minute security lockout.",
                ]);
        }

        // 4. Successful Authentication
        $user->update([
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        Log::info("User [{$user->email}] successfully authenticated with role [{$user->role}].");

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userEmail = Auth::user()?->email ?? 'unknown';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info("User [{$userEmail}] logged out successfully.");

        return redirect()
            ->route('login')
            ->with('status', 'You have been successfully logged out of CafeSync.');
    }

    /**
     * 1-Click Instant Demo Login for Portfolios & Evaluators.
     */
    public function demoLogin(Request $request, string $role): RedirectResponse
    {
        $roleMap = [
            'staff' => 'cashier@cafesync.local',
            'cashier' => 'cashier@cafesync.local',
            'barista' => 'barista@cafesync.local',
            'hrd' => 'hrd@cafesync.local',
            'hr' => 'hrd@cafesync.local',
            'manager' => 'manager@cafesync.local',
        ];

        $normalizedRole = strtolower(trim($role));
        $targetEmail = $roleMap[$normalizedRole] ?? null;

        if (! $targetEmail) {
            return redirect()->route('login')->withErrors(['email' => 'Unrecognized demo role. Please select Staff, HRD, or Manager.']);
        }

        $user = User::where('email', $targetEmail)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Demo account not found in database. Please run seeder: php artisan db:seed']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        Log::info("Demo session started for [{$user->email}] role [{$user->role}].");

        return $this->redirectBasedOnRole($user)->with(
            'success',
            "Welcome to the CafeSync Live Demo! You are currently exploring as {$user->full_name} ({$user->role})."
        );
    }

    /**
     * Determine redirect route based on user role.
     */
    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'STAFF' => redirect()->intended(route('pos.index')),
            'HRD' => redirect()->intended(route('hr.roster')),
            'MANAGER' => redirect()->intended(route('manager.dashboard')),
            default => redirect()->intended('/'),
        };
    }
}
