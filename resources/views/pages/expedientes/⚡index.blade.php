<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\Municipio;
use App\Models\Role;

new #[Title('- Expedientes')] class extends Component {
    // Variables de filtro
    public string $search = '';
    public string $estadoFiltro = '';
    public string $municipioFiltro = '';
    public string $tipoFiltro = '';
    public string $anioFiltro = '';

    public function mount()
    {
        $this->anioFiltro = (string) now()->year;
    }

    // Estadísticas generales (filtradas por acceso del usuario)
    #[Computed]
    public function estadisticas()
    {
        $user = auth()->user();
        $baseQuery = Expediente::query()->accesiblesPor($user);

        // Si hay filtro de año, aplicar
        $baseQuery->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro));

        return [
            'total' => (clone $baseQuery)->count(),
            'recibidos' => (clone $baseQuery)->recibidos()->count(),
            'en_revision' => (clone $baseQuery)->enRevision()->count(),
            'aprobados' => (clone $baseQuery)->aprobados()->count(),
            'rechazados' => (clone $baseQuery)->rechazados()->count(),
            'archivados' => (clone $baseQuery)->archivados()->count(),
        ];
    }

    // Municipios para el filtro select
    #[Computed]
    public function municipiosDisponibles()
    {
        $user = auth()->user();

        if ($user->isMunicipal()) {
            return $user->municipios()->ordenados()->get();
        }

        if ($user->isTecnico()) {
            return $user->municipios()->ordenados()->get();
        }

        return Municipio::activos()->ordenados()->get();
    }

    // Años disponibles
    #[Computed]
    public function aniosDisponibles()
    {
        $anios = Expediente::query()
            ->accesiblesPor(auth()->user())
            ->selectRaw('YEAR(fecha_recibido) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio')
            ->filter()
            ->toArray();

        if (empty($anios)) {
            $anios = [now()->year];
        }

        return $anios;
    }

    // Info del municipio del usuario municipal
    #[Computed]
    public function miMunicipio()
    {
        $user = auth()->user();
        if ($user->isMunicipal()) {
            return $user->municipios()->first();
        }
        return null;
    }

    // Limpiar filtros
    public function limpiarFiltros()
    {
        $this->search = '';
        $this->estadoFiltro = '';
        $this->municipioFiltro = '';
        $this->tipoFiltro = '';
        $this->anioFiltro = (string) now()->year;
    }
};
?>

@php
    $expedientesSubtitle = auth()->user()->isMunicipal()
        ? 'Expedientes de tu municipio'
        : (auth()->user()->isTecnico()
            ? 'Gestión de expedientes de tus municipios asignados'
            : 'Gestión y seguimiento de expedientes');
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <x-patterns.page-header title="Expedientes" tone="primary" :subtitle="$expedientesSubtitle" badge="Operación">
        <x-slot:icon>
            <x-heroicon-o-folder class="h-6 w-6" />
        </x-slot:icon>

        @can('create', Expediente::class)
            <x-slot:actions>
                <a href="{{ route('expedientes.create') }}" wire:navigate class="btn btn-primary gap-2">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    Nuevo Expediente
                </a>
            </x-slot:actions>
        @endcan
    </x-patterns.page-header>

    {{-- Info municipio para rol Municipal --}}
    @if (auth()->user()->isMunicipal() && $this->miMunicipio)
        <div class="card border border-primary/20 bg-linear-to-r from-primary/10 to-secondary/10 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div
                            class="bg-primary text-primary-content rounded-lg w-14 h-14 flex items-center justify-center">
                            <x-heroicon-o-building-office-2 class="w-7 h-7" />
                        </div>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg">{{ $this->miMunicipio->nombre }}</h2>
                        <p class="text-sm text-base-content/60">{{ $this->miMunicipio->departamento }}</p>
                    </div>
                    <div class="ml-auto">
                        <span
                            class="badge {{ $this->miMunicipio->estaActivo() ? 'badge-success' : 'badge-error' }} badge-lg">
                            {{ $this->miMunicipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="stat rounded-box border border-base-content/10 bg-base-100 p-3 shadow-sm">
            <div class="stat-figure text-base-content/20">
                <x-heroicon-o-folder class="w-5 h-5" />
            </div>
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat rounded-box border border-info/20 bg-info/5 p-3 shadow-sm">
            <div class="stat-figure text-info/40">
                <x-heroicon-o-inbox-arrow-down class="w-5 h-5" />
            </div>
            <div class="stat-title text-xs">Recibidos</div>
            <div class="stat-value text-lg text-info">{{ $this->estadisticas['recibidos'] }}</div>
        </div>
        <div class="stat rounded-box border border-warning/20 bg-warning/5 p-3 shadow-sm">
            <div class="stat-figure text-warning/40">
                <x-heroicon-o-clock class="w-5 h-5" />
            </div>
            <div class="stat-title text-xs">En Revisión</div>
            <div class="stat-value text-lg text-warning">{{ $this->estadisticas['en_revision'] }}</div>
        </div>
        <div class="stat rounded-box border border-success/20 bg-success/5 p-3 shadow-sm">
            <div class="stat-figure text-success/40">
                <x-heroicon-o-check-circle class="w-5 h-5" />
            </div>
            <div class="stat-title text-xs">Aprobados</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['aprobados'] }}</div>
        </div>
        <div class="stat rounded-box border border-error/20 bg-error/5 p-3 shadow-sm">
            <div class="stat-figure text-error/40">
                <x-heroicon-o-x-circle class="w-5 h-5" />
            </div>
            <div class="stat-title text-xs">Rechazados</div>
            <div class="stat-value text-lg text-error">{{ $this->estadisticas['rechazados'] }}</div>
        </div>
        <div class="stat rounded-box border border-base-content/10 bg-base-100 p-3 shadow-sm">
            <div class="stat-figure text-base-content/20">
                <x-heroicon-o-archive-box class="w-5 h-5" />
            </div>
            <div class="stat-title text-xs">Archivados</div>
            <div class="stat-value text-lg text-base-content/40">{{ $this->estadisticas['archivados'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-patterns.filter-card title="Filtros" description="Acota por búsqueda, estado, municipio y año." tone="base">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            {{-- Búsqueda --}}
            <div class="xl:col-span-2">
                <label class="input input-sm border-base-content/20 focus-within:border-primary">
                    <x-heroicon-o-magnifying-glass class="h-[1em] opacity-50" />
                    <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                        placeholder="Buscar por código SNIP o proyecto..." />
                    @if ($search)
                        <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                </label>
            </div>

            {{-- Estado --}}
            <select wire:model.live="estadoFiltro"
                class="select select-sm w-full border-base-content/20 focus:border-primary">
                <option value="">Todos los estados</option>
                @foreach (Expediente::getEstados() as $estado)
                    <option value="{{ $estado }}">{{ $estado }}</option>
                @endforeach
            </select>

            {{-- Municipio (oculto para Municipal) --}}
            @unless (auth()->user()->isMunicipal())
                <select wire:model.live="municipioFiltro"
                    class="select select-sm w-full border-base-content/20 focus:border-primary">
                    <option value="">Todos los municipios</option>
                    @foreach ($this->municipiosDisponibles as $mun)
                        <option value="{{ $mun->id }}">{{ $mun->nombre }}</option>
                    @endforeach
                </select>
            @endunless

            {{-- Año --}}
            <div class="flex gap-2">
                <select wire:model.live="anioFiltro"
                    class="select select-sm w-full border-base-content/20 focus:border-primary">
                    <option value="">Todos los años</option>
                    @foreach ($this->aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
                <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm btn-square tooltip tooltip-left"
                    data-tip="Limpiar filtros">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </button>
            </div>
        </div>
    </x-patterns.filter-card>

    {{-- Tabla de expedientes --}}
    <livewire:table.expediente-table :search="$search" :estadoFiltro="$estadoFiltro" :municipioFiltro="$municipioFiltro" :tipoFiltro="$tipoFiltro"
        :anioFiltro="$anioFiltro" />

    {{-- Modales --}}
    @if (auth()->user()->isAdmin())
        <livewire:modals.expediente-estado-modal />
        <livewire:modals.expediente-delete-modal />
    @endif

    @can('create', Expediente::class)
        <livewire:modals.expediente-enviar-revision-modal />
    @endcan
</div>
