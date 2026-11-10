<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use App\Support\CsrfToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRefreshCsrfToken
{
    public function __construct(
        protected CsrfToken $csrfToken
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Mobile refresh requests use Bearer tokens.
        if ($request->bearerToken()) {
            return $next($request);
        }

        // Web refresh requests must use the refresh cookie.
        $refreshCookie = $request->cookie('refresh_token');

        if (!$refreshCookie) {
            return ApiResponse::error(
                'Invalid refresh token request.',
                401
            );
        }

        $cookieToken = $request->cookie('XSRF-TOKEN');
        $headerToken = $request->header('X-CSRF-TOKEN');

        if (
            !$cookieToken ||
            !$headerToken ||
            !hash_equals($cookieToken, $headerToken) ||
            !$this->csrfToken->isValid($cookieToken)
        ) {
            return ApiResponse::error(
                'CSRF token mismatch.',
                419,
                'CSRF_TOKEN_MISMATCH'
            );
        }

        // Validate the request origin.
        $origin = $request->headers->get('Origin');

       $allowedOrigin = rtrim(config('app.frontend_url'), '/');

        if (
            !$origin ||
            rtrim($origin, '/') !== $allowedOrigin
        ) {
            return ApiResponse::error(
                'Invalid request origin.',
                403
            );
        }

        return $next($request);
    }
}
