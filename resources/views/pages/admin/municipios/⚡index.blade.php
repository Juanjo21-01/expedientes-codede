<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('- Municipios')] class extends Component {
    // Variables de filtro
    public $search = '';
    public $estadoFiltro = '';
};
?>

<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-building-library class="w-6 h-6" />
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
                                <x-heroicon-o-x-mark class="w-4 h-4" />
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
