<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Role;

new #[Title('- Usuarios')] class extends Component {
    // Variables de filtro
    public $search = '';
    public $rolFiltro = '';

    // Computed: Roles para filtros
    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    #[Computed]
    public function canManageUsuarios(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
};
?>

<div class="space-y-6">
    <!-- Header -->
    <x-patterns.page-header title="Gestión de Usuarios" subtitle="Administra usuarios, roles y acceso al sistema."
        tone="primary" badge="Administración">
        <x-slot:icon>
            <x-heroicon-o-users class="h-6 w-6" />
        </x-slot:icon>

        @if ($this->canManageUsuarios)
            <x-slot:actions>
                <button @click="$dispatch('crear-usuario')" class="btn btn-primary gap-2">
                    <x-heroicon-o-user-plus class="w-5 h-5" />
                    Nuevo Usuario
                </button>
            </x-slot:actions>
        @endif
    </x-patterns.page-header>

    <!-- Filtros -->
    <x-patterns.filter-card title="Filtros" description="Puedes buscar por nombre o correo y filtrar por rol."
        tone="base">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">Buscar</legend>
                    <label class="input input-sm flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="h-[1em] opacity-50" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                            placeholder="Buscar por nombre o correo..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </label>
                </fieldset>
            </div>
            <div class="w-full sm:w-56">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">Rol</legend>
                    <select wire:model.live="rolFiltro" class="select select-sm w-full">
                        <option value="">Todos los roles</option>
                        @foreach ($this->roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
        </div>
    </x-patterns.filter-card>

    <!-- Tabla -->
    <livewire:table.usuario-table :search="$search" :rolFiltro="$rolFiltro" />

    @if ($this->canManageUsuarios)
        <!-- Modal Crear / Editar -->
        <livewire:modals.usuario-modal />

        <!-- Modal Eliminar -->
        <livewire:modals.usuario-delete-modal />
    @endif
</div>
