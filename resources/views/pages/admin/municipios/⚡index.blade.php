<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;

new #[Title('- Municipios')] class extends Component {
    // Variables de filtro
    public $search = '';
    public $estadoFiltro = '';

    // Mensaje flash
    public $mensajeTipo = '';
    public $mensajeTexto = '';

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
        <div role="alert" class="alert alert-{{ $mensajeTipo }} shadow-lg mb-6">
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
            <button type="button" wire:click="cerrarMensaje" class="btn btn-sm btn-circle btn-ghost">
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
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 21h19.5M3.75 3v18m16.5-18v18M5.25 3h13.5M5.25 21h13.5M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                Gestión de Municipios
            </h1>
            <p class="text-base-content/60 text-sm mt-1">
                {{ auth()->user()->isAdmin() ? 'Administra los municipios de San Marcos' : 'Información de los municipios de San Marcos' }}
            </p>
        </div>

        <!-- Info: Total municipios -->
        <div class="stats shadow-sm border border-base-300">
            <div class="stat py-2 px-4">
                <div class="stat-title text-xs">Total Municipios</div>
                <div class="stat-value text-lg text-primary">30</div>
            </div>
        </div>
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
                            placeholder="Buscar municipio por nombre..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </label>
                </div>
                <div class="w-full sm:w-56">
                    <select wire:model.live="estadoFiltro"
                        class="select select-bordered w-full focus:select-primary transition-colors">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <livewire:table.municipio-table :search="$search" :estadoFiltro="$estadoFiltro" />

    <!-- Modal Editar (solo Admin) -->
    @if (auth()->user()->isAdmin())
    <livewire:modals.municipio-modal />
    @endif
</div>
