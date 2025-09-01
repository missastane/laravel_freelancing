<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModelBindingIsCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $admin = $request->route('customer');
        if($admin->user_type === 2){
            return response()->json([
                'status' => false,
                'message' => 'کاربری با این مشخصات یافت نشد'
            ],400);
        }
        return $next($request);
    }
}
