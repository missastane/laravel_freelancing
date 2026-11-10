<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Support\ApiResponse;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(
            \App\Http\Middleware\DetectExpiredJwtToken::class
        );

        $middleware->appendToGroup(
            'auth',
            \Tymon\JWTAuth\Http\Middleware\Authenticate::class
        );

        $middleware->alias([
            'jwt.expired' => \App\Http\Middleware\DetectExpiredJwtToken::class,
            'csrf.refresh' => \App\Http\Middleware\VerifyRefreshCsrfToken::class,
            'adminity' => \App\Http\Middleware\CheckModelBindingAdminity::class,
            'is_customer' => \App\Http\Middleware\CheckModelBindingIsCustomer::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'employer' => \App\Http\Middleware\Employer::class,
            'freelancer' => \App\Http\Middleware\Freelancer::class,
            'admin' => \App\Http\Middleware\Admin::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {

        // Email verification signature
        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->routeIs('verification.verify')) {
                return redirect()->away(
                    config('services.frontend_url') .
                    '/auth/email-verified?status=failed'
                );
            }
        });

        // Route not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('errors.route_not_found'),
                    404,
                    'NOT_FOUND'
                );
            }
        });

        // Model not found
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('errors.resource_not_found'),
                    404,
                    'RESOURCE_NOT_FOUND'
                );
            }
        });

        // JWT authentication errors
        $exceptions->renderable(function (UnauthorizedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = __('errors.please_do_login');
                $errorCode = 'AUTHENTICATION_REQUIRED';

                if ($e->getPrevious() instanceof TokenExpiredException) {
                    $message = __('errors.access_token_expired');
                    $errorCode = 'TOKEN_EXPIRED';
                }

                return ApiResponse::error(
                    $message,
                    401,
                    $errorCode
                );
            }
        });

        // Laravel authentication errors
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('errors.please_do_login'),
                    401,
                    'AUTHENTICATION_REQUIRED'
                );
            }
        });

        // Authorization errors
        $exceptions->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('errors.unauthorized'),
                    403,
                    'FORBIDDEN'
                );
            }
        });

        // Validation errors
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('errors.validation_failed'),
                    422,
                    'VALIDATION_ERROR',
                    $e->errors()
                );
            }
        });

        // Unexpected server errors
        $exceptions->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {

                \Log::error('Unhandled API Exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return ApiResponse::error(
                    __('errors.server_error'),
                    500,
                    'SERVER_ERROR'
                );
            }
        });

    })->create();
