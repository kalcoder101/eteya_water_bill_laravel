<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiSecret
{
    /**
     * Handle an incoming request.
     * Allows requests authenticated via Sanctum / web session,
     * OR matching the X-Api-Secret header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is authenticated via Sanctum or web auth guard
        if (Auth::guard('sanctum')->check() || Auth::check() || $request->user()) {
            return $next($request);
        }

        // 2. Check X-Api-Secret header for shared secret fallback
        $secret = config('app.api_secret', env('API_SHARED_SECRET', 'watersteward_secret_key'));
        $providedSecret = $request->header('X-Api-Secret') ?? $request->header('x-api-secret');

        if ($secret && $providedSecret && hash_equals($secret, $providedSecret)) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthenticated / Invalid API secret'], 401);
    }
}
