<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user->user_type != 2 && $user->active_role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'شما مجاز به انجام این عملیات نیستید'
            ], 403);
        }
        return $next($request);
    }
}
