<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('- Municipios')] class extends Component {
    // Variables de filtro
    public $search = '';
    public $estadoFiltro = '';
};
?>

@php
    $municipiosSubtitle = auth()->user()->isAdmin()
        ? 'Administra los municipios de San Marcos'
        : 'Información de los municipios de San Marcos';
@endphp

<div class="space-y-6">
    <!-- Header -->
    <x-patterns.page-header title="Gestión de Municipios" tone="primary" :subtitle="$municipiosSubtitle" badge="Administración">
        <x-slot:icon>
            <x-heroicon-o-building-library class="w-6 h-6" />
        </x-slot:icon>

        <x-slot:actions>
            <div class="rounded-box border border-base-content/10 bg-base-100/80 px-4 py-3 min-w-40 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-base-content/60">Total Municipios</p>
                <p class="text-2xl font-semibold text-primary leading-tight">30</p>
            </div>
        </x-slot:actions>
    </x-patterns.page-header>

    <!-- Filtros -->
    <x-patterns.filter-card title="Filtros" description="Busca por nombre de municipio y filtra por estado."
        tone="base">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">Buscar</legend>
                    <label class="input input-sm flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="h-[1em] opacity-50" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                            placeholder="Buscar municipio por nombre..." />
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
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">Estado</legend>
                    <select wire:model.live="estadoFiltro" class="select select-sm w-full">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </fieldset>
            </div>
        </div>
    </x-patterns.filter-card>

    <!-- Tabla -->
    <livewire:table.municipio-table :search="$search" :estadoFiltro="$estadoFiltro" />

    <!-- Modal Editar (solo Admin) -->
    @if (auth()->user()->isAdmin())
        <livewire:modals.municipio-modal />
    @endif
</div>
