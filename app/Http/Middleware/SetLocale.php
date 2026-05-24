<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. URL override
        $locale = $request->query('lang');

        // 2. Request preference (register)
        if (!$locale && $request->filled('lang_preference')) {
            $locale = $request->lang_preference;
        }

        // 3. Accept-Language fallback
        if (!$locale) {
            $header = $request->header('Accept-Language', '');
            $first = strtolower(explode(',', $header)[0] ?? '');
            $lang = substr($first, 0, 2);
            if (in_array($lang, ['en', 'ar'])) {
                $locale = $lang;
            }
        }

        // 4. Authenticated user
        $user = Auth::user();
        if (!$locale && $user && !empty($user->lang_preference)) {
            $locale = $user->lang_preference;
        }

        // 5. Default
        if (!$locale) {
            $locale = config('app.locale', 'en');
        }

        // ✅ FINAL SAFETY GUARD
        if (!is_string($locale) || !in_array($locale, ['en', 'ar'])) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
