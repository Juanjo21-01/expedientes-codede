<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioActivoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->estado) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->with('toast', [
                    'tipo' => 'warning',
                    'mensaje' => 'Tu cuenta está inactiva. Contacta al administrador.',
                ]);
        }

        return $next($request);
    }
}
