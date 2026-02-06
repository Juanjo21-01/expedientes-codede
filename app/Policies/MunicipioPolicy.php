<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Municipio;
use Illuminate\Auth\Access\HandlesAuthorization;

class MunicipioPolicy
{
    use HandlesAuthorization;

    /**
     * Ver listado de municipios (Admin y Director General)
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDirector();
    }

    /**
     * Ver detalle de municipio (Admin y Director General)
     */
    public function view(User $user, Municipio $municipio): bool
    {
        return $user->isAdmin() || $user->isDirector();
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
