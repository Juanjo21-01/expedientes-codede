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

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-document-check class="w-6 h-6" />
                </div>
                Gestión de Guías
            </h1>
            <p class="text-base-content/60 text-sm mt-1">Administrar guías y documentos del sistema</p>
        </div>

        @can('create', Guia::class)
            <a href="{{ route('admin.guias.create') }}" wire:navigate class="btn btn-primary gap-2">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                Subir Guía
            </a>
        @endcan
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Total Guías</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Activas</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['activas'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Inactivas</div>
            <div class="stat-value text-lg text-base-content/40">{{ $this->estadisticas['inactivas'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Categorías</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['categorias'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5 mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- Buscador --}}
                <div class="sm:col-span-2">
                    <label class="input input-sm">
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
                <select wire:model.live="categoriaFiltro" class="select select-sm w-full">
                    <option value="">Todas las categorías</option>
                    @foreach ($this->categorias as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>

                {{-- Estado --}}
                <div class="flex gap-2">
                    <select wire:model.live="estadoFiltro" class="select select-sm flex-1">
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
        </div>
    </div>

    {{-- Tabla --}}
    <livewire:table.guia-table :search="$search" :categoriaFiltro="$categoriaFiltro" :estadoFiltro="$estadoFiltro" />

    {{-- Modales --}}
    <livewire:modals.guia-pdf-modal />

    @if (auth()->user()->isAdmin())
        <livewire:modals.guia-estado-modal />
        <livewire:modals.guia-delete-modal />
    @endif
</div>
