<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale')
            ?? $request->user('customer')?->preferred_locale
            ?? config('app.locale', 'th');

        app()->setLocale(in_array($locale, ['th', 'en'], true) ? $locale : 'th');

        return $next($request);
    }
}
