<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', ['en']);
        $fallbackLocale = config('app.fallback_locale', 'en');

        $preferred = session('locale')
            ?? Auth::user()?->language?->code
            ?? $request->getPreferredLanguage($availableLocales)
            ?? config('app.locale', $fallbackLocale);

        if (! in_array($preferred, $availableLocales, true)) {
            $preferred = $fallbackLocale;
        }

        App::setLocale($preferred);
        App::setFallbackLocale($fallbackLocale);

        if ($request->session()->get('locale') !== $preferred) {
            $request->session()->put('locale', $preferred);
        }

        return $next($request);
    }
}
