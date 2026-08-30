<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isOwner(), 403, 'เฉพาะเจ้าของระบบเท่านั้น');

        return $next($request);
    }
}
