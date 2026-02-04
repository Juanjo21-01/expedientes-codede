<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Role;

new #[Title('- Usuarios')] class extends Component {
    // Variables de filtro
    public $search = '';
    public $rolFiltro = '';

    // Mensaje flash
    public $mensajeTipo = '';
    public $mensajeTexto = '';

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

    // Escuchar mensaje para mostrar
    #[On('mostrar-mensaje')]
    public function mostrarMensaje($tipo, $mensaje)
    {
        $this->mensajeTipo = $tipo;
        $this->mensajeTexto = $mensaje;
    }

    // Cerrar mensaje
    public function cerrarMensaje()
    {
        $this->mensajeTipo = '';
        $this->mensajeTexto = '';
    }
};
?>

<div>
    <!-- Mensajes Flash -->
    @if ($mensajeTexto)
        {{-- validar si es tipo warning tambien --}}
        <div role="alert" class="alert alert-{{ $mensajeTipo }} mb-6">
            @if ($mensajeTipo === 'success')
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif ($mensajeTipo === 'warning')
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
            <span>{{ $mensajeTexto }}</span>
            <button type="button" wire:click="cerrarMensaje" class="btn btn-sm btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
            <p class="text-base-content/60 text-sm mt-1">Administra los usuarios del sistema</p>
        </div>

        <button wire:click="crear" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
            Nuevo Usuario
        </button>
    </div>

    <!-- Filtros -->
    <div class="card bg-base-100 shadow-sm border border-base-300 mb-6">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                            class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd"
                                d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                                clip-rule="evenodd" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                            placeholder="Buscar por nombre o correo..." />
                    </label>
                </div>
                <div class="w-full sm:w-56">
                    <select wire:model.live="rolFiltro" class="select select-bordered w-full">
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
