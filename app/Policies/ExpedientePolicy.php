<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expediente;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ExpedientePolicy
{
    use HandlesAuthorization;

    // Lista general (todos ven, pero filtrada por scopeAccesiblesPor)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Ver detalle
    public function view(User $user, Expediente $expediente): bool
    {
        return match ($user->role->nombre) {
            Role::ADMIN, Role::DIRECTOR => true,
            Role::JEFE_FINANCIERO => in_array($expediente->estado, [
                Expediente::ESTADO_EN_REVISION,
                Expediente::ESTADO_COMPLETO,
                Expediente::ESTADO_INCOMPLETO,
                Expediente::ESTADO_APROBADO,
                Expediente::ESTADO_RECHAZADO,
            ]),
            Role::TECNICO, Role::MUNICIPAL => $user->municipios->contains($expediente->municipio_id),
            default => false,
        };
    }

    // Crear (Técnico y Admin)
    public function create(User $user): bool
    {
        return $user->isTecnico() || $user->isAdmin();
    }

    // Editar (Técnico solo en estados editables, Admin siempre excepto archivados)
    public function update(User $user, Expediente $expediente): bool
    {
        if ($user->isAdmin()) {
            return !$expediente->estaArchivado();
        }

        if ($user->isTecnico()) {
            return $user->municipios->contains($expediente->municipio_id) &&
                $expediente->esEditable();
        }

        return false;
    }

    // Revisión financiera (Jefe Financiero y Admin, expediente en revisión)
    public function revisarFinanciera(User $user, Expediente $expediente): bool
    {
        return ($user->isJefeFinanciero() || $user->isAdmin()) &&
            $expediente->estaEnRevision();
    }

    // Enviar a revisión (Técnico, desde Recibido)
    public function enviarRevision(User $user, Expediente $expediente): bool
    {
        return $user->isTecnico() &&
            $user->municipios->contains($expediente->municipio_id) &&
            $expediente->estaRecibido();
    }

    // Cambiar estado rápido (solo Admin)
    public function cambiarEstado(User $user, Expediente $expediente): bool
    {
        return $user->isAdmin() && !$expediente->estaArchivado();
    }

    // Eliminar (Admin, solo si NO tiene revisiones financieras y está en Recibido)
    public function delete(User $user, Expediente $expediente): bool
    {
        return $user->isAdmin() &&
            $expediente->estaRecibido() &&
            $expediente->revisionesFinancieras()->count() === 0;
    }

    // Archivar (Admin, cualquier estado excepto ya archivado)
    public function archivar(User $user, Expediente $expediente): bool
    {
        return $user->isAdmin() && !$expediente->estaArchivado();
    }

    public function restore(User $user, Expediente $expediente): bool
    {
        return false;
    }

    public function forceDelete(User $user, Expediente $expediente): bool
    {
        return false;
    }
}
