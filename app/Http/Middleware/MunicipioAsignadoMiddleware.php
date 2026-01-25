<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MunicipioAsignadoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()){
            return redirect()->route('login');
        }

        $user = auth()->user();
        $role = $user->role->nombre;

        // ROL -> ADMIN Y DIRECTOR VEN TODOS LOS MUNICIPIOS
        if (in_array($role, ['Administrador', 'Director', 'Jefe Administrativo-Financiero'])) {
            return $next($request);
        }

        // ROL -> TECNICO Y MUNICIPAL DEBEN DE TENER MUNICIPIO ASIGNADO
        $routeParam = $request->route('expediente') ?? $request->route('municipio_id');

        if ($routeParam && method_exists($routeParam, 'municipio_id')){
            $municipioId = $routeParam->municipio_id ?? $routeParam->municipio->id;

            if (!$user->municipios->contains('id', $municipioId)) {
                abort(403, 'Acceso Denegado: No tienes permiso para acceder a este municipio.');
            }
        }

        return $next($request);
    }
}
