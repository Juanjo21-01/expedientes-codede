<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Bitacora;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $rolNombre = $user->role?->nombre ?? 'Sin rol';

        Bitacora::registrarCreacionUsuario(
            "Usuario {$user->nombre_completo} creado – Email: {$user->email}, Rol: {$rolNombre}",
            $user->id
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Si cambió el estado (activar/desactivar) → registrar como cambio de estado
        if ($user->isDirty('estado')) {
            $nuevoEstado = $user->estado ? 'Activado' : 'Desactivado';

            Bitacora::registrarCambioEstadoUsuario(
                "Usuario {$user->nombre_completo} fue {$nuevoEstado}",
                $user->id
            );
            return;
        }

        // Si cambió el rol → registrar específicamente
        if ($user->isDirty('role_id')) {
            $rolAnterior = \App\Models\Role::find($user->getOriginal('role_id'))?->nombre ?? 'Sin rol';
            $rolNuevo = $user->role?->nombre ?? 'Sin rol';

            Bitacora::registrarEdicionUsuario(
                "Usuario {$user->nombre_completo} – Cambio de rol: {$rolAnterior} → {$rolNuevo}",
                $user->id
            );
            return;
        }

        // Edición general
        $camposModificados = array_keys($user->getDirty());
        $camposModificados = array_diff($camposModificados, [
            'updated_at', 'created_at', 'remember_token',
            'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
            'password',
        ]);

        if (!empty($camposModificados)) {
            Bitacora::registrarEdicionUsuario(
                "Usuario {$user->nombre_completo} actualizado – Campos: " . implode(', ', $camposModificados),
                $user->id
            );
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Bitacora::registrarEliminacionUsuario(
            "Usuario {$user->nombre_completo} eliminado – Email: {$user->email}",
            $user->id
        );
    }
}
