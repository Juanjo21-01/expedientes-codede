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
                    <label class="input flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="h-[1em] opacity-50" />
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
                    <select wire:model.live="estadoFiltro" class="select w-full">
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
