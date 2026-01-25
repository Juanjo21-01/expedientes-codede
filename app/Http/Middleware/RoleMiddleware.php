<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check() || !in_array(auth()->user()->rol->nombre, $roles)) {
            abort(403, 'Acceso no autorizado');
        }

        return $next($request);
    }
}
