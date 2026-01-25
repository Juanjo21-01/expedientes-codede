<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Guia;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GuiaPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Guia $guia)
    {
        return true; // Todos ven la guía actual
    }

    public function create(User $user)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function update(User $user, Guia $guia)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function delete(User $user, Guia $guia)
    {
        return false; // No borrar versiones
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Guia $guia): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Guia $guia): bool
    {
        return false;
    }
}
