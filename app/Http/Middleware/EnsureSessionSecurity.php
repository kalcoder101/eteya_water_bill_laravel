<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EnsureSessionSecurity
{
    /** 30 minutes idle timeout */
    public const SESSION_IDLE_TIMEOUT = 1800;

    /** 8 hours maximum lifetime */
    public const SESSION_MAX_LIFETIME = 28800;

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $now = time();
        if (! Session::has('session_started')) {
            Session::put('session_started', $now);
        }
        if (! Session::has('last_activity')) {
            Session::put('last_activity', $now);
        }

        $sessionStarted = (int) Session::get('session_started');
        $lastActivity   = (int) Session::get('last_activity');

        if (($now - $lastActivity) > self::SESSION_IDLE_TIMEOUT
            || ($now - $sessionStarted) > self::SESSION_MAX_LIFETIME) {
            app(\App\Services\AuditService::class)
                ->logActivity(Auth::user()->fullName(), 'Session Timeout Logout');

            Auth::logout();
            Session::flush();

            return redirect()->route('login', ['timeout' => 1]);
        }

        Session::put('last_activity', $now);

        return $next($request);
    }
}
