<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Municipio;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MunicipioPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function create(User $user)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function update(User $user, Municipio $municipio)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function delete(User $user, Municipio $municipio)
    {
        return $user->role->nombre === 'Administrador';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Municipio $municipio): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Municipio $municipio): bool
    {
        return false;
    }
}
