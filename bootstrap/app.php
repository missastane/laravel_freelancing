<?php

use App\Exceptions\WrongCurrentPasswordException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('auth', \Tymon\JWTAuth\Http\Middleware\Authenticate::class);
         $middleware->alias([
            'adminity' => \App\Http\Middleware\CheckModelBindingAdminity::class,
            'is_customer' => \App\Http\Middleware\CheckModelBindingIsCustomer::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'employer' => \App\Http\Middleware\Employer::class,
            'freelancer' => \App\Http\Middleware\Freelancer::class,
            'admin' => \App\Http\Middleware\Admin::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
         $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'مسیر مورد نظر پیدا نشد'
                ], 404);
            }
        });
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'موردی با این مشخصات پیدا نشد'
                ], 404);
            }
        });
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید'
                ], 401);
            }
        });
       
    })->create();
