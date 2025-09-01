<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Freelancer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user->user_type != 1 && $user->active_role !== 'freelancer') {
            return response()->json([
                'status' => false,
                'message' => 'شما مجوز انجام این عملیات را ندارید'
            ], 403);
        }
        return $next($request);
    }
}
