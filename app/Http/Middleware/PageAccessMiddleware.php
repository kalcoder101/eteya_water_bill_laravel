<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageAccessMiddleware
{
    /**
     * Usage: ->middleware('page.access:bills')
     * Verifies that the logged-in user is allowed to view the given page key
     * (using the page_role_permissions() helper).
     */
    public function handle(Request $request, Closure $next, string $page)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! is_allowed_page($page, $user->job_role)) {
            abort(403, 'Access denied. Required role for page: '.str_replace('-', ' ', $page));
        }

        return $next($request);
    }
}
