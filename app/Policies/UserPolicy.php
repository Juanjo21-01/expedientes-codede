<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function view(User $user, User $model)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function create(User $user)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function update(User $user, User $model)
    {
        return $user->role->nombre === 'Administrador';
    }

    public function delete(User $user, User $model)
    {
        return $user->role->nombre === 'Administrador';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
