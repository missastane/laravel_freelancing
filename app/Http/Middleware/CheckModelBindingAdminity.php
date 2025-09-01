<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModelBindingAdminity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->route('admin');
        if($admin->user_type !== 2){
            return response()->json([
                'status' => false,
                'message' => 'ادمین با این مشخصات یافت نشد'
            ],400);
        }
        return $next($request);
    }
}
