<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Role;
use App\Models\User;

new #[title('- Usuarios')] class extends Component {
    // Variables
    public $roles;
    public $filtro, $search, $usuarioId;

    // Constructor
    public function mount()
    {
        $this->roles = Role::all();
    }

    // Crear
    public function crear()
    {
        $this->dispatch('crearUsuario');
    }
};
?>

<div class="">

    <!-- Inicio -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Gestión de Usuarios</h1>

        <button wire:click="crear" class="btn btn-primary">Nuevo Usuario</button>
    </div>

    <!-- Filtros y busqueda -->
    <div class="flex gap-4 mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..."
            class="input input-bordered" />
        <select wire:model.live="filtro" class="select select-bordered">
            <option value="">Todos roles</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
            @endforeach
        </select>
    </div>

    <!-- Tabla -->
    <livewire:table.usuario-table :rolFiltro="$filtro" :buscar="$search" />

    <!-- Modal -->
    {{-- <livewire:modals.modal-base :id="$usuarioId" title="Usuario"> --}}
        {{-- <livewire:forms.usuario-form :id="$usuarioId" /> --}}
    {{-- </livewire:modals.modal-base> --}}
</div>
