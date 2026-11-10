<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
class DetectExpiredJwtToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only inspect API requests.
        if (!$request->is('api/*')) {
            return $next($request);
        }

        // The refresh endpoint must accept an expired access token.
        if ($request->is('api/auth/refresh')) {
            return $next($request);
        }

        $token = $request->bearerToken();

        if (!$token) {
            return $next($request);
        }

        try {
            JWTAuth::setToken($token)->getPayload();

        } catch (TokenExpiredException $e) {
            return ApiResponse::error(
                __('errors.access_token_expired'),
                401,
                'TOKEN_EXPIRED'
            );

        } catch (TokenBlacklistedException $e) {
            return ApiResponse::error(
                __('errors.token_black_listed'),
                401,
                'TOKEN_BLACKLISTED'
            );
        }

        return $next($request);
    }
}