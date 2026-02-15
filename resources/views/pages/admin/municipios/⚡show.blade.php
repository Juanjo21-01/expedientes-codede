<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Municipio;
use App\Models\Expediente;
use App\Models\Role;
use Carbon\Carbon;

new #[Title('- Detalle Municipio')] class extends Component {
    public Municipio $municipio;

    // Filtro de año para expedientes
    public string $anioFiltro = '';

    // Montar el componente
    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio->load(['expedientes.tipoSolicitud', 'expedientes.responsable']);
        $this->anioFiltro = (string) now()->year;
    }

    // Refrescar cuando se edita el municipio
    #[On('municipio-guardado')]
    public function refrescar()
    {
        $this->municipio = $this->municipio->fresh(['expedientes.tipoSolicitud', 'expedientes.responsable']);
    }

    // Emitir evento para editar (solo Admin)
    public function editar()
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->dispatch('abrir-modal-municipio', municipioId: $this->municipio->id);
    }

    // Cambiar estado del municipio (solo Admin)
    public function cambiarEstado()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($this->municipio->estaActivo()) {
            $this->municipio->desactivar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Municipio desactivado correctamente.');
        } else {
            $this->municipio->activar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Municipio activado correctamente.');
        }
        $this->municipio = $this->municipio->fresh();
    }

    // Abrir modal de notificación
    public function notificar()
    {
        $this->dispatch('abrir-notificacion-modal', municipioId: $this->municipio->id);
    }

    // Años disponibles para filtro
    #[Computed]
    public function aniosDisponibles()
    {
        $anios = $this->municipio->expedientes()->selectRaw('YEAR(fecha_recibido) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->toArray();

        // Si no hay expedientes, mostrar al menos el año actual
        if (empty($anios)) {
            $anios = [now()->year];
        }

        return $anios;
    }

    // Usuario Municipal asignado
    #[Computed]
    public function usuarioMunicipal()
    {
        return $this->municipio->users()->whereHas('role', fn($q) => $q->where('nombre', Role::MUNICIPAL))->where('users.estado', true)->first();
    }

    // Técnicos asignados
    #[Computed]
    public function tecnicosAsignados()
    {
        return $this->municipio->users()->whereHas('role', fn($q) => $q->where('nombre', Role::TECNICO))->where('users.estado', true)->get();
    }

    // Estadísticas generales del municipio
    #[Computed]
    public function estadisticas()
    {
        $expedientes = $this->municipio->expedientes;

        return [
            'total' => $expedientes->count(),
            'recibidos' => $expedientes->where('estado', Expediente::ESTADO_RECIBIDO)->count(),
            'en_revision' => $expedientes->where('estado', Expediente::ESTADO_EN_REVISION)->count(),
            'completos' => $expedientes->where('estado', Expediente::ESTADO_COMPLETO)->count(),
            'incompletos' => $expedientes->where('estado', Expediente::ESTADO_INCOMPLETO)->count(),
            'aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
            'archivados' => $expedientes->where('estado', Expediente::ESTADO_ARCHIVADO)->count(),
        ];
    }

    // Estadísticas filtradas por año
    #[Computed]
    public function estadisticasAnio()
    {
        $expedientes = $this->municipio->expedientes()->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))->get();

        return [
            'total' => $expedientes->count(),
            'recibidos' => $expedientes->where('estado', Expediente::ESTADO_RECIBIDO)->count(),
            'en_revision' => $expedientes->where('estado', Expediente::ESTADO_EN_REVISION)->count(),
            'completos' => $expedientes->where('estado', Expediente::ESTADO_COMPLETO)->count(),
            'incompletos' => $expedientes->where('estado', Expediente::ESTADO_INCOMPLETO)->count(),
            'aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
            'archivados' => $expedientes->where('estado', Expediente::ESTADO_ARCHIVADO)->count(),
        ];
    }

    // Expedientes del año seleccionado
    #[Computed]
    public function expedientesAnio()
    {
        return $this->municipio
            ->expedientes()
            ->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))
            ->with(['tipoSolicitud', 'responsable'])
            ->orderBy('fecha_recibido', 'desc')
            ->get();
    }

    // Datos para la gráfica de expedientes por mes del año seleccionado
    #[Computed]
    public function chartData()
    {
        $anio = $this->anioFiltro ?: now()->year;

        $expedientesPorMes = $this->municipio->expedientes()->whereYear('fecha_recibido', $anio)->selectRaw('MONTH(fecha_recibido) as mes, COUNT(*) as cantidad')->groupBy('mes')->pluck('cantidad', 'mes')->toArray();

        $labels = [];
        $data = [];
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $meses[$i - 1];
            $data[] = $expedientesPorMes[$i] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    // Datos para gráfica de estados (pie chart)
    #[Computed]
    public function chartEstados()
    {
        $stats = $this->estadisticasAnio;
        return [
            'labels' => ['Recibidos', 'En Revisión', 'Completos', 'Incompletos', 'Aprobados', 'Rechazados', 'Archivados'],
            'data' => [$stats['recibidos'], $stats['en_revision'], $stats['completos'], $stats['incompletos'], $stats['aprobados'], $stats['rechazados'], $stats['archivados']],
            'colors' => [
                'rgba(59, 130, 246, 0.7)', // info - Recibidos
                'rgba(234, 179, 8, 0.7)', // warning - En Revisión
                'rgba(34, 197, 94, 0.7)', // success - Completos
                'rgba(249, 115, 22, 0.7)', // orange - Incompletos
                'rgba(16, 185, 129, 0.7)', // emerald - Aprobados
                'rgba(239, 68, 68, 0.7)', // error - Rechazados
                'rgba(148, 163, 184, 0.7)', // slate - Archivados
            ],
        ];
    }

    // Reset de computadas cuando cambia el año
    public function updatedAnioFiltro()
    {
        unset($this->estadisticasAnio);
        unset($this->expedientesAnio);
        unset($this->chartData);
        unset($this->chartEstados);
    }
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm font-medium">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="gap-1 text-base-content/60 hover:text-primary transition-colors">
                    <x-heroicon-o-home class="w-4 h-4" />
                    Inicio
                </a>
            </li>
            <li>
                <a href="{{ route('admin.municipios.index') }}" wire:navigate
                    class="gap-1 text-base-content/60 hover:text-primary transition-colors">
                    <x-heroicon-o-building-library class="w-4 h-4" />
                    Municipios
                </a>
            </li>
            <li>
                <span class="inline-flex items-center gap-1 text-primary">
                    <x-heroicon-o-map-pin class="w-4 h-4" />
                    {{ $municipio->nombre }}
                </span>
            </li>
        </ul>
    </div>

    {{-- Tarjeta Principal del Municipio --}}
    <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-xl border border-base-300 overflow-hidden">
        {{-- Header decorativo --}}
        <div class="h-20 bg-gradient-to-r from-primary/20 via-accent/20 to-secondary/20"></div>

        <div class="card-body -mt-12">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Ícono del municipio --}}
                <div class="indicator">
                    <span class="indicator-item indicator-bottom indicator-end">
                        <div class="inline-grid *:[grid-area:1/1]">
                            @if ($municipio->estaActivo())
                                <div class="status status-success animate-ping"></div>
                                <div class="status status-success"></div>
                            @else
                                <div class="status status-error"></div>
                            @endif
                        </div>
                    </span>
                    <div
                        class="bg-primary text-primary-content rounded-full w-24 h-24 ring-4 ring-base-100 shadow-lg flex justify-center items-center">
                        <x-heroicon-o-building-office-2 class="w-12 h-12" />
                    </div>
                </div>

                {{-- Información principal --}}
                <div class="flex-1 text-center sm:text-left">
                    <h1 class="text-3xl font-bold tracking-tight">{{ $municipio->nombre }}</h1>
                    <p class="text-base-content/70 text-lg">{{ $municipio->departamento }}</p>

                    <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                        <div
                            class="badge badge-lg gap-2 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                            <div class="inline-grid *:[grid-area:1/1]">
                                @if ($municipio->estaActivo())
                                    <div class="status status-success animate-ping"></div>
                                    <div class="status status-success"></div>
                                @else
                                    <div class="status status-error"></div>
                                @endif
                            </div>
                            {{ $municipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                        </div>
                        @if ($municipio->tieneContactoCompleto())
                            <div class="badge badge-lg badge-outline badge-info gap-1">
                                <x-heroicon-o-check class="w-3 h-3" />
                                Contacto completo
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Acciones (solo Admin) --}}
                @if (auth()->user()->isAdmin())
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button wire:click="cambiarEstado"
                            class="btn {{ $municipio->estaActivo() ? 'btn-outline btn-error' : 'btn-success' }} btn-sm gap-2">
                            @if ($municipio->estaActivo())
                                <x-heroicon-o-no-symbol class="w-4 h-4" />
                                Desactivar
                            @else
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                Activar
                            @endif
                        </button>
                        <button wire:click="editar" class="btn btn-primary btn-sm gap-2">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar contacto
                        </button>
                        @if ($municipio->tieneEmailContacto())
                            <button wire:click="notificar" class="btn btn-info btn-sm gap-2">
                                <x-heroicon-o-envelope class="w-4 h-4" />
                                Notificar
                            </button>
                        @endif
                    </div>
                @else
                    {{-- Notificar para Director/Jefe/Técnico --}}
                    @if ($municipio->tieneEmailContacto())
                        <div class="flex gap-2">
                            <button wire:click="notificar" class="btn btn-info btn-sm gap-2">
                                <x-heroicon-o-envelope class="w-4 h-4" />
                                Notificar
                            </button>
                        </div>
                    @endif
                @endif
            </div>

            <div class="divider my-4"></div>

            {{-- Detalles en grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Contacto --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 text-primary rounded-btn p-3">
                            <x-heroicon-o-user class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Contacto</p>
                            <p class="font-semibold">{{ $municipio->contacto_nombre ?? 'Sin asignar' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Email --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-info/10 text-info rounded-btn p-3">
                            <x-heroicon-o-envelope class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Email</p>
                            <p class="font-semibold text-sm">{{ $municipio->contacto_email ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Teléfono --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-success/10 text-success rounded-btn p-3">
                            <x-heroicon-o-phone class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Teléfono</p>
                            <p class="font-semibold">{{ $municipio->contacto_telefono ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-warning/10 text-warning rounded-btn p-3">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Observaciones</p>
                            <p class="font-semibold text-sm truncate">
                                {{ $municipio->observaciones ?? 'Sin observaciones' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Usuarios Asignados --}}
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-lg gap-3">
                <div class="bg-accent/10 text-accent rounded-btn p-2">
                    <x-heroicon-o-users class="w-5 h-5" />
                </div>
                Usuarios Asignados
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                {{-- Usuario Municipal --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-ghost badge-sm">Municipal</span>
                    </div>
                    @if ($this->usuarioMunicipal)
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-neutral text-neutral-content rounded-full w-10 h-10 flex items-center justify-center">
                                    <span class="text-sm">{{ $this->usuarioMunicipal->iniciales }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold truncate">{{ $this->usuarioMunicipal->nombre_completo }}</p>
                                <p class="text-sm text-base-content/60 truncate">{{ $this->usuarioMunicipal->email }}
                                </p>
                                @if ($this->usuarioMunicipal->telefono)
                                    <p class="text-xs text-base-content/50">Tel:
                                        {{ $this->usuarioMunicipal->telefono }}</p>
                                @endif
                            </div>
                            @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                                <a href="{{ route('admin.usuarios.show', $this->usuarioMunicipal->id) }}" wire:navigate
                                    class="btn btn-ghost btn-sm btn-circle" title="Ver perfil">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="text-base-content/40 text-sm italic flex items-center gap-2">
                            <x-heroicon-o-no-symbol class="w-5 h-5" />
                            Sin usuario municipal asignado
                        </div>
                    @endif
                </div>

                {{-- Técnicos Asignados --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-info badge-sm">Técnico(s)</span>
                        <span class="badge badge-ghost badge-xs">{{ $this->tecnicosAsignados->count() }}</span>
                    </div>
                    @if ($this->tecnicosAsignados->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($this->tecnicosAsignados as $tecnico)
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div
                                            class="bg-info text-info-content rounded-full w-10 h-10 flex items-center justify-center">
                                            <span class="text-sm">{{ $tecnico->iniciales }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold truncate">{{ $tecnico->nombre_completo }}</p>
                                        <p class="text-sm text-base-content/60 truncate">{{ $tecnico->email }}</p>
                                    </div>
                                    @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                                        <a href="{{ route('admin.usuarios.show', $tecnico->id) }}" wire:navigate
                                            class="btn btn-ghost btn-sm btn-circle" title="Ver perfil">
                                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-base-content/40 text-sm italic flex items-center gap-2">
                            <x-heroicon-o-no-symbol class="w-5 h-5" />
                            Sin técnicos asignados
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas Generales --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Expedientes --}}
        <div
            class="stat bg-gradient-to-br from-primary/5 to-primary/10 rounded-box shadow border border-primary/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-primary opacity-80">
                <x-heroicon-o-folder-open class="w-10 h-10" />
            </div>
            <div class="stat-title text-primary/70">Total Expedientes</div>
            <div class="stat-value text-primary">{{ $this->estadisticas['total'] }}</div>
            <div class="stat-desc">Histórico</div>
        </div>

        {{-- Aprobados --}}
        <div
            class="stat bg-gradient-to-br from-success/5 to-success/10 rounded-box shadow border border-success/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-success opacity-80">
                <x-heroicon-o-check-circle class="w-10 h-10" />
            </div>
            <div class="stat-title text-success/70">Aprobados</div>
            <div class="stat-value text-success">{{ $this->estadisticas['aprobados'] }}</div>
            <div class="stat-desc">Finalizados</div>
        </div>

        {{-- En proceso --}}
        <div
            class="stat bg-gradient-to-br from-warning/5 to-warning/10 rounded-box shadow border border-warning/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-warning opacity-80">
                <x-heroicon-o-clock class="w-10 h-10" />
            </div>
            <div class="stat-title text-warning/70">En Proceso</div>
            <div class="stat-value text-warning">
                {{ $this->estadisticas['recibidos'] + $this->estadisticas['en_revision'] + $this->estadisticas['completos'] + $this->estadisticas['incompletos'] }}
            </div>
            <div class="stat-desc">Activos</div>
        </div>

        {{-- Rechazados --}}
        <div
            class="stat bg-gradient-to-br from-error/5 to-error/10 rounded-box shadow border border-error/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-error opacity-80">
                <x-heroicon-o-x-circle class="w-10 h-10" />
            </div>
            <div class="stat-title text-error/70">Rechazados</div>
            <div class="stat-value text-error">{{ $this->estadisticas['rechazados'] }}</div>
            <div class="stat-desc">No aprobados</div>
        </div>
    </div>

    {{-- Sección por Año con Filtro --}}
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <h2 class="card-title text-lg gap-3">
                    <div class="bg-secondary/10 text-secondary rounded-btn p-2">
                        <x-heroicon-o-calendar class="w-5 h-5" />
                    </div>
                    Expedientes por Año
                </h2>

                <select wire:model.live="anioFiltro" class="select select-sm w-32">
                    @foreach ($this->aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Badges de estados del año --}}
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="badge badge-info gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['recibidos'] }}</span> Recibidos
                </span>
                <span class="badge badge-warning gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['en_revision'] }}</span> En Revisión
                </span>
                <span class="badge badge-success badge-outline gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['completos'] }}</span> Completos
                </span>
                <span class="badge badge-warning badge-outline gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['incompletos'] }}</span> Incompletos
                </span>
                <span class="badge badge-success gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['aprobados'] }}</span> Aprobados
                </span>
                <span class="badge badge-error gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['rechazados'] }}</span> Rechazados
                </span>
                <span class="badge badge-ghost gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['archivados'] }}</span> Archivados
                </span>
            </div>

            {{-- Gráficas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                {{-- Gráfica de barras por mes --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <h3 class="font-semibold text-sm mb-3 text-base-content/70">Expedientes por Mes —
                        {{ $anioFiltro }}</h3>
                    <div class="w-full h-64" wire:ignore x-data="barChart(@js($this->chartData))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartData))">
                        <canvas x-ref="barChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                {{-- Gráfica de pie por estados --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <h3 class="font-semibold text-sm mb-3 text-base-content/70">Distribución por Estado —
                        {{ $anioFiltro }}</h3>
                    <div class="w-full h-64" wire:ignore x-data="pieChart(@js($this->chartEstados))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartEstados))">
                        <canvas x-ref="pieChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Expedientes del Año --}}
    @if ($this->expedientesAnio->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="card-title text-lg gap-3">
                        <div class="bg-info/10 text-info rounded-btn p-2">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                        </div>
                        Expedientes {{ $anioFiltro }}
                        <span class="badge badge-neutral badge-sm">{{ $this->expedientesAnio->count() }}</span>
                    </h2>
                </div>

                <div class="overflow-x-auto mt-3">
                    <table class="table table-zebra table-pin-rows">
                        <thead>
                            <tr class="bg-base-200">
                                <th class="text-center">No.</th>
                                <th>Código SNIP</th>
                                <th>Proyecto</th>
                                <th>Tipo Solicitud</th>
                                <th>Tipo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Fecha Recibido</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->expedientesAnio as $index => $expediente)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="text-center font-medium">{{ $index + 1 }}</td>
                                    <td>
                                        <span
                                            class="font-mono font-bold text-primary">{{ $expediente->codigo_snip }}</span>
                                    </td>
                                    <td>
                                        <div class="tooltip tooltip-right"
                                            data-tip="{{ $expediente->nombre_proyecto }}">
                                            <div class="max-w-xs truncate font-medium">
                                                {{ $expediente->nombre_proyecto }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="text-sm text-base-content/70">{{ $expediente->tipoSolicitud->nombre ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-outline badge-xs">{{ $expediente->ordinario_extraordinario ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge badge-sm gap-1
                                            @if ($expediente->estado === 'Aprobado') badge-success
                                            @elseif($expediente->estado === 'Rechazado') badge-error
                                            @elseif($expediente->estado === 'En Revisión') badge-warning
                                            @elseif($expediente->estado === 'Recibido') badge-info
                                            @elseif($expediente->estado === 'Completo') badge-success badge-outline
                                            @elseif($expediente->estado === 'Incompleto') badge-warning badge-outline
                                            @elseif($expediente->estado === 'Archivado') badge-ghost
                                            @else badge-ghost @endif">
                                            @if ($expediente->estado === 'Aprobado')
                                                <x-heroicon-o-check class="w-3 h-3" />
                                            @elseif($expediente->estado === 'Rechazado')
                                                <x-heroicon-o-x-mark class="w-3 h-3" />
                                            @endif
                                            {{ $expediente->estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="text-sm text-base-content/60">{{ $expediente->fecha_recibido?->format('d/m/Y') ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if ($expediente->responsable)
                                            <span
                                                class="text-sm">{{ $expediente->responsable->nombre_completo }}</span>
                                        @else
                                            <span class="text-base-content/40">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <div class="flex flex-col items-center justify-center py-8 gap-3">
                    <x-heroicon-o-folder-open class="w-16 h-16 text-base-content/20" />
                    <p class="text-base-content/50 text-lg">No hay expedientes para el año {{ $anioFiltro }}</p>
                    <p class="text-base-content/40 text-sm">Selecciona otro año o verifica que existan expedientes
                        registrados.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Editar Municipio (solo Admin) --}}
    @if (auth()->user()->isAdmin())
        <livewire:modals.municipio-modal />
    @endif

    {{-- Modal de notificación --}}
    <livewire:modals.notificacion-modal />
</div>

{{-- Scripts para Chart.js --}}
@script
    <script>
        Alpine.data('barChart', (initialData) => ({
            chart: null,
            initChart() {
                const ctx = this.$refs.barChart.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: initialData.labels,
                        datasets: [{
                            label: 'Expedientes',
                            data: initialData.data,
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(59, 130, 246, 0.8)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: (ctx) =>
                                        `${ctx.parsed.y} expediente${ctx.parsed.y !== 1 ? 's' : ''}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            },
            updateChart(newData) {
                if (this.chart) {
                    this.chart.data.labels = newData.labels;
                    this.chart.data.datasets[0].data = newData.data;
                    this.chart.update('active');
                }
            }
        }));

        Alpine.data('pieChart', (initialData) => ({
            chart: null,
            initChart() {
                const ctx = this.$refs.pieChart.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: initialData.labels,
                        datasets: [{
                            data: initialData.data,
                            backgroundColor: initialData.colors,
                            borderWidth: 2,
                            borderColor: 'rgba(255, 255, 255, 0.8)',
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 11
                                    },
                                    padding: 12,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) =>
                                        ` ${ctx.label}: ${ctx.parsed} expediente${ctx.parsed !== 1 ? 's' : ''}`
                                }
                            }
                        },
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            },
            updateChart(newData) {
                if (this.chart) {
                    this.chart.data.labels = newData.labels;
                    this.chart.data.datasets[0].data = newData.data;
                    this.chart.data.datasets[0].backgroundColor = newData.colors;
                    this.chart.update('active');
                }
            }
        }));
    </script>
@endscript
