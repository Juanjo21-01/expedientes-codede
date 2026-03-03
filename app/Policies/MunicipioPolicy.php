<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Municipio;
use Illuminate\Auth\Access\HandlesAuthorization;

class MunicipioPolicy
{
    use HandlesAuthorization;

    /**
     * Ver listado de municipios (Admin, Director, Jefe Financiero, Técnico y Municipal)
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $user->isDirector()
            || $user->isJefeFinanciero()
            || $user->isTecnico()
            || $user->isMunicipal();
    }

    /**
     * Ver detalle de municipio
     * - Admin, Director y Jefe Financiero: acceso global
     * - Técnico y Municipal: solo municipios asignados
     */
    public function view(User $user, Municipio $municipio): bool
    {
        if ($user->isAdmin() || $user->isDirector() || $user->isJefeFinanciero()) {
            return true;
        }

        if ($user->isTecnico() || $user->isMunicipal()) {
            return $user->tieneAccesoAMunicipio($municipio->id);
        }

        return false;
    }

    /**
     * No se permite crear municipios (son fijos desde seeder)
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Editar datos de contacto y observaciones (solo Admin)
     */
    public function update(User $user, Municipio $municipio): bool
    {
        return $user->isAdmin();
    }

    /**
     * No se permite eliminar municipios
     */
    public function delete(User $user, Municipio $municipio): bool
    {
        return false;
    }

    /**
     * No se permite restaurar
     */
    public function restore(User $user, Municipio $municipio): bool
    {
        return false;
    }

    /**
     * No se permite eliminar permanentemente
     */
    public function forceDelete(User $user, Municipio $municipio): bool
    {
        return false;
    }
}
