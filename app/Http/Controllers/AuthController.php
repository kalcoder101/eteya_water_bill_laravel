<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'error'   => $request->query('timeout') ? 'Session expired. Please sign in again.' : '',
            'success' => '',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['username']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['username' => "Too many login attempts. Please try again in {$seconds} seconds."])
                         ->withInput($request->except('password'));
        }

        $user = User::where('user_name', $credentials['username'])->first();
        if (! $user) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors(['username' => 'Incorrect username and password.'])
                         ->withInput($request->except('password'));
        }

        $stored = $user->user_password;
        $ok = false;
        if ($user->passwordIsHashed()) {
            $ok = Hash::check($credentials['password'], $stored);
        } else {
            $ok = $stored === $credentials['password'];
            if ($ok) {
                // Transparently re-hash and persist
                $user->user_password = Hash::make($credentials['password']);
                $user->save();
            }
        }

        if (! $ok) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors(['username' => 'Incorrect username and password.'])
                         ->withInput($request->except('password'));
        }

        RateLimiter::clear($throttleKey);

        $remember = $request->boolean('remember');
        Auth::login($user, $remember);
        Session::regenerate(true);
        Session::put([
            'session_started' => time(),
            'last_activity'   => time(),
            'full_name'       => $user->fullName(),
            'job_role'        => $user->job_role,
            'user_name'       => $user->user_name,
        ]);

        app(AuditService::class)->logActivity($user->fullName(), 'Logging to System');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            app(AuditService::class)
                ->logActivity(Auth::user()->fullName(), 'Logout to System');
        }

        Auth::logout();
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
