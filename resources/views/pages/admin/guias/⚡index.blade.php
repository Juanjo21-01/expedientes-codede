<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Guia;

new #[Title('- Gestión de Guías')] class extends Component {
    public string $search = '';
    public string $categoriaFiltro = '';
    public string $estadoFiltro = '';

    #[Computed]
    public function estadisticas()
    {
        return [
            'total' => Guia::count(),
            'activas' => Guia::activas()->count(),
            'inactivas' => Guia::inactivas()->count(),
            'categorias' => count(Guia::categoriasDisponibles()),
        ];
    }

    #[Computed]
    public function categorias()
    {
        return Guia::categoriasDisponibles();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'categoriaFiltro', 'estadoFiltro']);
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <x-patterns.page-header title="Gestión de Guías" tone="primary"
        subtitle="Administra documentos, estado de publicación y versiones por categoría." badge="Administración">
        <x-slot:icon>
            <x-heroicon-o-document-check class="h-6 w-6" />
        </x-slot:icon>

        @can('create', Guia::class)
            <x-slot:actions>
                <a href="{{ route('admin.guias.create') }}" wire:navigate class="btn btn-primary gap-2">
                    <x-heroicon-o-arrow-up-tray class="h-5 w-5" />
                    Subir Guía
                </a>
            </x-slot:actions>
        @endcan
    </x-patterns.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="stat rounded-box border border-base-content/10 bg-base-100 p-3 shadow-sm">
            <div class="stat-title text-xs">Total Guías</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat rounded-box border border-success/20 bg-success/5 p-3 shadow-sm">
            <div class="stat-title text-xs">Activas</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['activas'] }}</div>
        </div>
        <div class="stat rounded-box border border-warning/20 bg-warning/5 p-3 shadow-sm">
            <div class="stat-title text-xs">Inactivas</div>
            <div class="stat-value text-lg text-warning">{{ $this->estadisticas['inactivas'] }}</div>
        </div>
        <div class="stat rounded-box border border-info/20 bg-info/5 p-3 shadow-sm">
            <div class="stat-title text-xs">Categorías</div>
            <div class="stat-value text-info text-lg">{{ $this->estadisticas['categorias'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-patterns.filter-card title="Filtros" description="Busca por título/categoría y filtra por estado."
        tone="base">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Buscador --}}
            <div class="sm:col-span-2">
                <label class="input input-sm border-base-content/20 focus-within:border-primary">
                    <x-heroicon-o-magnifying-glass class="h-[1em] opacity-50" />
                    <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                        placeholder="Buscar por título o categoría..." />
                    @if ($search)
                        <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                </label>
            </div>

            {{-- Categoría --}}
            <select wire:model.live="categoriaFiltro"
                class="select select-sm w-full border-base-content/20 focus:border-primary">
                <option value="">Todas las categorías</option>
                @foreach ($this->categorias as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            {{-- Estado --}}
            <div class="flex gap-2">
                <select wire:model.live="estadoFiltro"
                    class="select select-sm flex-1 border-base-content/20 focus:border-primary">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activas</option>
                    <option value="inactivo">Inactivas</option>
                </select>
                <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm btn-square tooltip tooltip-left"
                    data-tip="Limpiar filtros">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </button>
            </div>
        </div>
    </x-patterns.filter-card>

    {{-- Tabla --}}
    <livewire:table.guia-table :search="$search" :categoriaFiltro="$categoriaFiltro" :estadoFiltro="$estadoFiltro" />

    {{-- Modales --}}
    <livewire:modals.guia-pdf-modal />

    @if (auth()->user()->isAdmin())
        <livewire:modals.guia-estado-modal />
        <livewire:modals.guia-delete-modal />
    @endif
</div>
