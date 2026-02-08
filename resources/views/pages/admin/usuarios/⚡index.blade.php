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

    // Abrir modal para crear usuario
    public function crear()
    {
        $this->dispatch('crear-usuario');
    }
};
?>

<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-users class="w-6 h-6" />
                </div>
                Gestión de Usuarios
            </h1>
            <p class="text-base-content/60 text-sm mt-1">Administra los usuarios del sistema</p>
        </div>

        <button wire:click="crear" class="btn btn-primary gap-2 shadow-md hover:shadow-lg transition-shadow">
            <x-heroicon-o-user-plus class="w-5 h-5" />
            Nuevo Usuario
        </button>
    </div>

    <!-- Filtros -->
    <div class="card bg-base-100 shadow-sm border border-base-300 mb-6">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label
                        class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                            class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd"
                                d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                                clip-rule="evenodd" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                            placeholder="Buscar por nombre o correo..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </label>
                </div>
                <div class="w-full sm:w-56">
                    <select wire:model.live="rolFiltro"
                        class="select select-bordered w-full focus:select-primary transition-colors">
                        <option value="">Todos los roles</option>
                        @foreach ($this->roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <livewire:table.usuario-table :search="$search" :rolFiltro="$rolFiltro" />

    <!-- Modal Crear / Editar -->
    <livewire:modals.usuario-modal />

    <!-- Modal Eliminar -->
    <livewire:modals.usuario-delete-modal />
</div>
