<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Example usage in routes:
     *
     *   Route::get('account-register', ...)->middleware('role:System Admin');
     *   Route::get('bills', ...)->middleware('role:System Admin|Manager|Customer Service');
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = [];
        foreach ($roles as $roleDef) {
            foreach (explode('|', $roleDef) as $r) {
                $allowed[] = trim($r);
            }
        }

        if (! in_array($user->job_role, $allowed, true)) {
            abort(403, 'Access denied. Required role: '.implode(' or ', $allowed));
        }

        return $next($request);
    }
}
