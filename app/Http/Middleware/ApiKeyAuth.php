<?php

namespace App\Http\Middleware;

use App\Helper\ApiResponse;
use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates via an `api-key` request header instead of the standard
 * `Authorization: Bearer` scheme, matching it against api_keys.key.
 */
class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('api-key');

        if (! $apiKey) {
            return ApiResponse::error('Missing api-key header.', 401);
        }

        $record = ApiKey::where('key', $apiKey)->with('user')->first();

        if (! $record || ! $record->user) {
            return ApiResponse::error('Invalid api-key.', 401);
        }

        $request->setUserResolver(fn () => $record->user);

        return $next($request);
    }
}
