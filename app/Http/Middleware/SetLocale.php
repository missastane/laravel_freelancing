<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['en']);

        $user = auth()->guard('api')->user();

        if ($user) {
            // Authenticated users use their saved locale.
            $locale = $user->locale;
        } else {
            // Guests use the locale sent in the request header.
            $locale = $request->header('X-Locale');
        }

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}