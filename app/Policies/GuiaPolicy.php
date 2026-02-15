<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Guia;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuiaPolicy
{
    use HandlesAuthorization;

    /**
     * Ver guías (todos los usuarios autenticados)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ver una guía individual (todos)
     */
    public function view(User $user, Guia $guia): bool
    {
        return true;
    }

    /**
     * Acceder al panel admin de guías (Admin, Director, Jefe Financiero)
     */
    public function adminAccess(User $user): bool
    {
        return $user->isAdmin() || $user->isDirector() || $user->isJefeFinanciero();
    }

    /**
     * Crear/subir guías (Admin, Director, Jefe Financiero)
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDirector() || $user->isJefeFinanciero();
    }

    /**
     * Editar guías (solo Admin)
     */
    public function update(User $user, Guia $guia): bool
    {
        return $user->isAdmin();
    }

    /**
     * Eliminar guías (solo Admin)
     */
    public function delete(User $user, Guia $guia): bool
    {
        return $user->isAdmin();
    }

    /**
     * Activar/desactivar guías (solo Admin)
     */
    public function toggleEstado(User $user, Guia $guia): bool
    {
        return $user->isAdmin();
    }
}
