<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expediente;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ExpedientePolicy
{
    use HandlesAuthorization;

    // Lista general (todos ven, pero filtrada por middleware/query)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Ver detalle
    public function view(User $user, Expediente $expediente): bool
    {
        return match ($user->role->nombre) {
            'Administrador', 'Director General' => true,
            'Jefe Administrativo-Financiero' => in_array($expediente->estado, ['En Revisión', 'Completo', 'Incompleto']),
            'Técnico', 'Municipal' => $user->municipios->contains($expediente->municipio_id),
            default => false,
        };
    }

    // Crear (solo Técnico)
    public function create(User $user): bool
    {
        return $user->role->nombre === 'Técnico';
    }

    // Editar (Técnico solo en Recibido/Rechazado/Incompleto, Admin override)
    public function update(User $user, Expediente $expediente): bool
    {
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        if ($user->role->nombre === 'Técnico') {
            return $user->municipios->contains($expediente->municipio_id) &&
                in_array($expediente->estado, ['Recibido', 'Rechazado', 'Incompleto']);
        }

        return false;
    }

    // Acción revisión financiera (solo Jefe, y expediente en revisión)
    public function revisarFinanciera(User $user, Expediente $expediente): bool
    {
        return $user->role->nombre === 'Jefe Administrativo-Financiero' &&
            $expediente->estado === 'En Revisión';
    }

    // Enviar a revisión (Técnico, desde Recibido)
    public function enviarRevision(User $user, Expediente $expediente): bool
    {
        return $user->role->nombre === 'Técnico' &&
            $user->municipios->contains($expediente->municipio_id) &&
            $expediente->estado === 'Recibido';
    }

    // Borrar (solo Administrador)
    public function delete(User $user, Expediente $expediente)
    {
        return $user->role->nombre === 'Administrador';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Expediente $expediente): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Expediente $expediente): bool
    {
        return false;
    }
}
