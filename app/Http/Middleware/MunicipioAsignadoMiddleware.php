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
        if (in_array($role, ['Administrador', 'Director General', 'Jefe Administrativo-Financiero'])) {
            return $next($request);
        }

        // ROL -> TECNICO Y MUNICIPAL DEBEN DE TENER MUNICIPIO ASIGNADO
        $expediente = $request->route('expediente');
        $municipioId = $request->route('municipio_id');

        // Si viene un expediente en la ruta
        if ($expediente) {
            // Si es un objeto Expediente (route model binding)
            if ($expediente instanceof \App\Models\Expediente) {
                $municipioId = $expediente->municipio_id;
            } elseif (is_numeric($expediente)) {
                // Si es un ID, buscar el expediente
                $exp = \App\Models\Expediente::find($expediente);
                $municipioId = $exp ? $exp->municipio_id : null;
            }
        }

        // Verificar que el usuario tenga asignado el municipio
        if ($municipioId && !$user->municipios->contains('id', $municipioId)) {
            abort(403, 'Acceso Denegado: No tienes permiso para acceder a este municipio.');
        }

        return $next($request);
    }
}
