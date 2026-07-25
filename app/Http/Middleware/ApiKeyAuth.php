<?php

namespace App\Http\Middleware;

use App\Helper\ApiResponse;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates via an `api-key` request header instead of the standard
 * `Authorization: Bearer` scheme, matching it against users.api_key.
 */
class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('api-key');

        if (! $apiKey) {
            return ApiResponse::error('Missing api-key header.', 401);
        }

        $user = User::where('api_key', $apiKey)->first();

        if (! $user) {
            return ApiResponse::error('Invalid api-key.', 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
