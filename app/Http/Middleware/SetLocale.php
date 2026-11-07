<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales.
     */
    protected array $supportedLocales = ['km', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if lang is requested in URL query string
        if ($request->has('lang') && in_array($request->query('lang'), $this->supportedLocales, true)) {
            $locale = $request->query('lang');
            Session::put('locale', $locale);
        } else {
            // 2. Read from session or fall back to application config
            $locale = Session::get('locale', config('app.locale', 'km'));
        }

        if (!in_array($locale, $this->supportedLocales, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}

