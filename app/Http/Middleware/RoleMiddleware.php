<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $nombreRol = auth()->user()?->role?->nombre;

        if (!auth()->check() || !$nombreRol || !in_array($nombreRol, $roles, true)) {
            abort(403, 'Acceso Denegado');
        }

        return $next($request);
    }
}
