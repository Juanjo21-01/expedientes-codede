<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\Municipio;
use App\Models\TipoSolicitud;
use App\Models\Role;

new #[Title('- Expedientes')] class extends Component {
    // Variables de filtro
    public string $search = '';
    public string $estadoFiltro = '';
    public string $municipioFiltro = '';
    public string $tipoSolicitudFiltro = '';
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
            'completos' => (clone $baseQuery)->completos()->count(),
            'incompletos' => (clone $baseQuery)->incompletos()->count(),
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

    // Tipos de solicitud
    #[Computed]
    public function tiposSolicitud()
    {
        return TipoSolicitud::ordenados()->get();
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
        $this->tipoSolicitudFiltro = '';
        $this->tipoFiltro = '';
        $this->anioFiltro = (string) now()->year;
    }
};
?>

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-folder class="w-6 h-6" />
                </div>
                Expedientes
            </h1>
            <p class="text-base-content/60 text-sm mt-1">
                @if (auth()->user()->isMunicipal())
                    Expedientes de tu municipio
                @elseif (auth()->user()->isTecnico())
                    Gestión de expedientes de tus municipios asignados
                @else
                    Gestión y seguimiento de expedientes
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Botón Crear (Técnico y Admin) --}}
            @can('create', Expediente::class)
                <a href="{{ route('expedientes.create') }}" wire:navigate class="btn btn-primary gap-2">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    Nuevo Expediente
                </a>
            @endcan
        </div>
    </div>

    {{-- Info municipio para rol Municipal --}}
    @if (auth()->user()->isMunicipal() && $this->miMunicipio)
        <div class="card bg-linear-to-r from-primary/10 to-secondary/10 border border-primary/20 shadow-sm mb-6">
            <div class="card-body p-4">
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-lg w-14 h-14">
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
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Recibidos</div>
            <div class="stat-value text-lg text-info">{{ $this->estadisticas['recibidos'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">En Revisión</div>
            <div class="stat-value text-lg text-warning">{{ $this->estadisticas['en_revision'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Completos</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['completos'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Incompletos</div>
            <div class="stat-value text-lg text-error">{{ $this->estadisticas['incompletos'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Aprobados</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['aprobados'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Rechazados</div>
            <div class="stat-value text-lg text-error">{{ $this->estadisticas['rechazados'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Archivados</div>
            <div class="stat-value text-lg text-base-content/40">{{ $this->estadisticas['archivados'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5 mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                {{-- Búsqueda --}}
                <div class="xl:col-span-2">
                    <label class="input input-sm">
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
                <select wire:model.live="estadoFiltro" class="select select-sm w-full">
                    <option value="">Todos los estados</option>
                    @foreach (App\Models\Expediente::getEstados() as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>

                {{-- Municipio (oculto para Municipal) --}}
                @unless (auth()->user()->isMunicipal())
                    <select wire:model.live="municipioFiltro" class="select select-sm w-full">
                        <option value="">Todos los municipios</option>
                        @foreach ($this->municipiosDisponibles as $mun)
                            <option value="{{ $mun->id }}">{{ $mun->nombre }}</option>
                        @endforeach
                    </select>
                @endunless

                {{-- Tipo Solicitud --}}
                <select wire:model.live="tipoSolicitudFiltro" class="select select-sm w-full">
                    <option value="">Tipo solicitud</option>
                    @foreach ($this->tiposSolicitud as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>

                {{-- Año --}}
                <div class="flex gap-2">
                    <select wire:model.live="anioFiltro" class="select select-sm w-full">
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
        </div>
    </div>

    {{-- Tabla de expedientes --}}
    <livewire:table.expediente-table :search="$search" :estadoFiltro="$estadoFiltro" :municipioFiltro="$municipioFiltro" :tipoSolicitudFiltro="$tipoSolicitudFiltro"
        :tipoFiltro="$tipoFiltro" :anioFiltro="$anioFiltro" />

    {{-- Modales (solo Admin) --}}
    @if (auth()->user()->isAdmin())
        <livewire:modals.expediente-estado-modal />
        <livewire:modals.expediente-delete-modal />
    @endif
</div>
