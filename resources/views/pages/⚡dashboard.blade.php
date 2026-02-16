<?php

use App\Models\Bitacora;
use App\Models\Expediente;
use App\Models\Guia;
use App\Models\Municipio;
use App\Models\RevisionFinanciera;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {

    // ---- Helpers de rol ----

    #[Computed]
    public function user()
    {
        return Auth::user()->load('role', 'municipios');
    }

    #[Computed]
    public function isGlobal(): bool
    {
        return $this->user->hasGlobalAccess();
    }

    // ---- Query base según rol ----

    private function expedienteBase()
    {
        return Expediente::query()->accesiblesPor($this->user);
    }

    // ===============================================================
    //  ESTADÍSTICAS — STAT CARDS
    // ===============================================================

    #[Computed]
    public function stats(): array
    {
        $user = $this->user;
        $base = $this->expedienteBase();

        // Stats comunes
        $total = (clone $base)->count();
        $activos = (clone $base)->activos()->count();
        $esteMes = (clone $base)->deEsteMes()->count();

        // KPIs
        $finalizados = (clone $base)->finalizados()->count();
        $aprobados = (clone $base)->aprobados()->count();
        $tasaAprobacion = $finalizados > 0 ? round(($aprobados / $finalizados) * 100, 1) : 0;

        // Tiempo promedio de trámite (solo aprobados con fecha_aprobacion)
        $tiempoPromedio = (clone $base)
            ->aprobados()
            ->whereNotNull('fecha_aprobacion')
            ->whereNotNull('fecha_recibido')
            ->selectRaw('AVG(DATEDIFF(fecha_aprobacion, fecha_recibido)) as promedio')
            ->value('promedio');
        $tiempoPromedio = $tiempoPromedio ? round($tiempoPromedio) : 0;

        // Monto total contratado
        $montoTotal = (clone $base)->sum('monto_contrato');

        // Stats específicos por rol
        if ($user->isAdmin() || $user->isDirector()) {
            return [
                ['label' => 'Total Expedientes', 'value' => $total, 'icon' => 'folder-open', 'color' => 'text-primary'],
                ['label' => 'Activos', 'value' => $activos, 'icon' => 'clock', 'color' => 'text-info'],
                ['label' => 'Este Mes', 'value' => $esteMes, 'icon' => 'calendar-days', 'color' => 'text-success'],
                ['label' => 'Monto Contratado', 'value' => 'Q ' . number_format($montoTotal, 2), 'icon' => 'banknotes', 'color' => 'text-warning'],
                ['label' => 'Tasa Aprobación', 'value' => $tasaAprobacion . '%', 'icon' => 'check-badge', 'color' => 'text-success'],
                ['label' => 'Tiempo Prom. (días)', 'value' => $tiempoPromedio, 'icon' => 'clock', 'color' => 'text-accent'],
                ['label' => 'Municipios Activos', 'value' => Municipio::activos()->count(), 'icon' => 'building-library', 'color' => 'text-secondary'],
                ['label' => 'Usuarios Activos', 'value' => User::activos()->count(), 'icon' => 'users', 'color' => 'text-primary'],
            ];
        }

        if ($user->isJefeFinanciero()) {
            $enRevision = (clone $base)->enRevision()->count();
            $revisionesMes = RevisionFinanciera::deEsteMes()->count();
            $montoAprobado = RevisionFinanciera::completas()
                ->whereHas('expediente', fn($q) => $q->accesiblesPor($user))
                ->sum('monto_aprobado');
            $pendientes = RevisionFinanciera::pendientesComplemento()->count();

            return [
                ['label' => 'En Revisión', 'value' => $enRevision, 'icon' => 'magnifying-glass', 'color' => 'text-warning'],
                ['label' => 'Revisiones este Mes', 'value' => $revisionesMes, 'icon' => 'clipboard-document-check', 'color' => 'text-info'],
                ['label' => 'Monto Aprobado', 'value' => 'Q ' . number_format($montoAprobado, 2), 'icon' => 'banknotes', 'color' => 'text-success'],
                ['label' => 'Pend. Complemento', 'value' => $pendientes, 'icon' => 'exclamation-triangle', 'color' => 'text-error'],
                ['label' => 'Total Expedientes', 'value' => $total, 'icon' => 'folder-open', 'color' => 'text-primary'],
                ['label' => 'Tasa Aprobación', 'value' => $tasaAprobacion . '%', 'icon' => 'check-badge', 'color' => 'text-success'],
            ];
        }

        if ($user->isTecnico()) {
            $pendientes = (clone $base)->whereIn('estado', [Expediente::ESTADO_RECIBIDO, Expediente::ESTADO_EN_REVISION])->count();
            $incompletos = (clone $base)->incompletos()->count();

            return [
                ['label' => 'Mis Expedientes', 'value' => $total, 'icon' => 'folder-open', 'color' => 'text-primary'],
                ['label' => 'Pendientes', 'value' => $pendientes, 'icon' => 'clock', 'color' => 'text-warning'],
                ['label' => 'Incompletos', 'value' => $incompletos, 'icon' => 'exclamation-triangle', 'color' => 'text-error'],
                ['label' => 'Aprobados este Mes', 'value' => (clone $base)->aprobados()->deEsteMes()->count(), 'icon' => 'check-badge', 'color' => 'text-success'],
            ];
        }

        // Municipal
        $enProceso = (clone $base)->activos()->count();
        return [
            ['label' => 'Mis Expedientes', 'value' => $total, 'icon' => 'folder-open', 'color' => 'text-primary'],
            ['label' => 'En Proceso', 'value' => $enProceso, 'icon' => 'clock', 'color' => 'text-info'],
            ['label' => 'Aprobados', 'value' => $aprobados, 'icon' => 'check-badge', 'color' => 'text-success'],
            ['label' => 'Este Mes', 'value' => $esteMes, 'icon' => 'calendar-days', 'color' => 'text-accent'],
        ];
    }

    // ===============================================================
    //  GRÁFICAS
    // ===============================================================

    /**
     * Distribución de expedientes por estado (dona)
     */
    #[Computed]
    public function chartEstados(): array
    {
        $base = $this->expedienteBase();

        $estados = Expediente::getEstados();
        $colores = [
            Expediente::ESTADO_RECIBIDO => '#3b82f6',
            Expediente::ESTADO_EN_REVISION => '#f59e0b',
            Expediente::ESTADO_COMPLETO => '#10b981',
            Expediente::ESTADO_INCOMPLETO => '#ef4444',
            Expediente::ESTADO_APROBADO => '#22c55e',
            Expediente::ESTADO_RECHAZADO => '#dc2626',
            Expediente::ESTADO_ARCHIVADO => '#6b7280',
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($estados as $estado) {
            $count = (clone $base)->deEstado($estado)->count();
            if ($count > 0) {
                $labels[] = $estado;
                $data[] = $count;
                $colors[] = $colores[$estado] ?? '#94a3b8';
            }
        }

        return compact('labels', 'data', 'colors');
    }

    /**
     * Expedientes por municipio (barras horizontales — top 10)
     */
    #[Computed]
    public function chartMunicipios(): array
    {
        $base = $this->expedienteBase();

        $municipios = (clone $base)
            ->select('municipio_id', DB::raw('COUNT(*) as total'))
            ->groupBy('municipio_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('municipio:id,nombre')
            ->get();

        return [
            'labels' => $municipios->pluck('municipio.nombre')->toArray(),
            'data' => $municipios->pluck('total')->toArray(),
        ];
    }

    /**
     * Tendencia mensual — últimos 6 meses (recibidos vs aprobados)
     */
    #[Computed]
    public function chartTendencia(): array
    {
        $labels = [];
        $recibidos = [];
        $aprobadosMes = [];

        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $labels[] = $fecha->translatedFormat('M Y');

            $mesBase = (clone $this->expedienteBase())
                ->whereYear('fecha_recibido', $fecha->year)
                ->whereMonth('fecha_recibido', $fecha->month);

            $recibidos[] = (clone $mesBase)->count();

            $aprobadosMes[] = (clone $this->expedienteBase())
                ->aprobados()
                ->whereNotNull('fecha_aprobacion')
                ->whereYear('fecha_aprobacion', $fecha->year)
                ->whereMonth('fecha_aprobacion', $fecha->month)
                ->count();
        }

        return compact('labels', 'recibidos', 'aprobadosMes');
    }

    /**
     * Revisiones por acción (solo Jefe Financiero)
     */
    #[Computed]
    public function chartRevisiones(): array
    {
        $acciones = [
            RevisionFinanciera::ACCION_APROBAR => ['label' => 'Aprobadas', 'color' => '#22c55e'],
            RevisionFinanciera::ACCION_RECHAZAR => ['label' => 'Rechazadas', 'color' => '#ef4444'],
            RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES => ['label' => 'Correcciones', 'color' => '#f59e0b'],
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($acciones as $accion => $config) {
            $count = RevisionFinanciera::where('accion', $accion)->count();
            $labels[] = $config['label'];
            $data[] = $count;
            $colors[] = $config['color'];
        }

        return compact('labels', 'data', 'colors');
    }

    // ===============================================================
    //  ACTIVIDAD RECIENTE (Bitácora)
    // ===============================================================

    #[Computed]
    public function actividadReciente()
    {
        $query = Bitacora::with('user')->recientes();

        // Jefe financiero: solo revisiones y expedientes
        if ($this->user->isJefeFinanciero()) {
            $query->whereIn('entidad', [Bitacora::ENTIDAD_EXPEDIENTE]);
        }

        // Técnico: solo de sus municipios (a través de expedientes)
        if ($this->user->isTecnico() || $this->user->isMunicipal()) {
            $municipioIds = $this->user->municipios_ids;
            $expedienteIds = Expediente::deMunicipios($municipioIds)->pluck('id');

            $query->where(function ($q) use ($expedienteIds) {
                $q->where(function ($sub) use ($expedienteIds) {
                    $sub->where('entidad', Bitacora::ENTIDAD_EXPEDIENTE)
                        ->whereIn('entidad_id', $expedienteIds);
                })->orWhere('user_id', $this->user->id);
            });
        }

        return $query->limit(8)->get();
    }

    // ===============================================================
    //  EXPEDIENTES RECIENTES
    // ===============================================================

    #[Computed]
    public function expedientesRecientes()
    {
        return $this->expedienteBase()
            ->with(['municipio:id,nombre', 'tipoSolicitud:id,nombre'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    // ===============================================================
    //  ACCESOS RÁPIDOS
    // ===============================================================

    #[Computed]
    public function accesosRapidos(): array
    {
        $user = $this->user;
        $accesos = [];

        // Todos pueden ver expedientes
        $accesos[] = ['label' => 'Ver Expedientes', 'route' => 'expedientes.index', 'icon' => 'folder-open', 'color' => 'btn-primary'];

        if ($user->isAdmin() || $user->isTecnico()) {
            $accesos[] = ['label' => 'Nuevo Expediente', 'route' => 'expedientes.create', 'icon' => 'plus-circle', 'color' => 'btn-success'];
        }

        // Guías - todos
        $accesos[] = ['label' => 'Ver Guías', 'route' => 'guias', 'icon' => 'clipboard-document-list', 'color' => 'btn-accent'];

        if ($user->isAdmin() || $user->isDirector() || $user->isJefeFinanciero() || $user->isTecnico()) {
            $accesos[] = ['label' => 'Reportes', 'route' => 'reportes', 'icon' => 'chart-bar', 'color' => 'btn-info'];
        }

        if ($user->isAdmin() || $user->isDirector()) {
            $accesos[] = ['label' => 'Bitácora', 'route' => 'admin.bitacora', 'icon' => 'clock', 'color' => 'btn-warning'];
        }

        if ($user->isAdmin()) {
            $accesos[] = ['label' => 'Usuarios', 'route' => 'admin.usuarios.index', 'icon' => 'users', 'color' => 'btn-secondary'];
        }

        return $accesos;
    }
};
?>

<div>
    {{-- Banner de bienvenida --}}
    <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent rounded-box p-5 mb-6 border border-primary/20">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">
                    ¡Bienvenido, {{ $this->user->nombres }}!
                </h1>
                <p class="text-base-content/60 text-sm mt-1">
                    <span class="badge badge-sm badge-primary badge-outline">{{ $this->user->role->nombre }}</span>
                    <span class="ml-2">{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($this->accesosRapidos as $acceso)
                    <a href="{{ route($acceso['route']) }}" wire:navigate
                        class="btn btn-sm {{ $acceso['color'] }} btn-outline gap-1">
                        @switch($acceso['icon'])
                            @case('folder-open')
                                <x-heroicon-o-folder-open class="w-4 h-4" />
                            @break

                            @case('plus-circle')
                                <x-heroicon-o-plus-circle class="w-4 h-4" />
                            @break

                            @case('clipboard-document-list')
                                <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                            @break

                            @case('chart-bar')
                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                            @break

                            @case('clock')
                                <x-heroicon-o-clock class="w-4 h-4" />
                            @break

                            @case('users')
                                <x-heroicon-o-users class="w-4 h-4" />
                            @break
                        @endswitch
                        {{ $acceso['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 {{ count($this->stats) > 6 ? 'lg:grid-cols-4' : 'lg:grid-cols-' . min(count($this->stats), 4) }} gap-4 mb-6">
        @foreach ($this->stats as $stat)
            <div class="stat bg-base-100 rounded-box shadow-sm border border-base-300 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title text-xs">{{ $stat['label'] }}</div>
                        <div class="stat-value text-xl {{ $stat['color'] }}">{{ $stat['value'] }}</div>
                    </div>
                    <div class="{{ $stat['color'] }} opacity-30">
                        @switch($stat['icon'])
                            @case('folder-open')
                                <x-heroicon-o-folder-open class="w-8 h-8" />
                            @break

                            @case('clock')
                                <x-heroicon-o-clock class="w-8 h-8" />
                            @break

                            @case('calendar-days')
                                <x-heroicon-o-calendar-days class="w-8 h-8" />
                            @break

                            @case('banknotes')
                                <x-heroicon-o-banknotes class="w-8 h-8" />
                            @break

                            @case('check-badge')
                                <x-heroicon-o-check-badge class="w-8 h-8" />
                            @break

                            @case('building-library')
                                <x-heroicon-o-building-library class="w-8 h-8" />
                            @break

                            @case('users')
                                <x-heroicon-o-users class="w-8 h-8" />
                            @break

                            @case('magnifying-glass')
                                <x-heroicon-o-magnifying-glass class="w-8 h-8" />
                            @break

                            @case('clipboard-document-check')
                                <x-heroicon-o-clipboard-document-check class="w-8 h-8" />
                            @break

                            @case('exclamation-triangle')
                                <x-heroicon-o-exclamation-triangle class="w-8 h-8" />
                            @break
                        @endswitch
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Gráfica: Distribución por Estado (dona) --}}
        <div class="bg-base-100 rounded-box shadow-sm border border-base-300 p-5">
            <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                <x-heroicon-o-chart-pie class="w-5 h-5 text-primary" />
                Distribución por Estado
            </h3>
            @if (count($this->chartEstados['data']) > 0)
                <div class="flex justify-center" wire:ignore
                    x-data="dashboardDonutChart(@js($this->chartEstados))" x-init="initChart()"
                    x-effect="updateChart(@js($this->chartEstados))">
                    <canvas x-ref="donutChart" style="max-height: 280px;"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-48 text-base-content/40">
                    <p>No hay datos disponibles</p>
                </div>
            @endif
        </div>

        {{-- Gráfica: Tendencia Mensual (líneas) --}}
        @if ($this->user->hasGlobalAccess() || $this->user->isTecnico())
            <div class="bg-base-100 rounded-box shadow-sm border border-base-300 p-5">
                <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-success" />
                    Tendencia Mensual (últimos 6 meses)
                </h3>
                <div wire:ignore x-data="dashboardTendenciaChart(@js($this->chartTendencia))"
                    x-init="initChart()" x-effect="updateChart(@js($this->chartTendencia))">
                    <canvas x-ref="tendenciaChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        @endif

        {{-- Gráfica: Expedientes por Municipio (barras horizontales) --}}
        @if ($this->user->hasGlobalAccess())
            <div class="bg-base-100 rounded-box shadow-sm border border-base-300 p-5">
                <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                    <x-heroicon-o-building-library class="w-5 h-5 text-accent" />
                    Top Municipios con más Expedientes
                </h3>
                @if (count($this->chartMunicipios['data']) > 0)
                    <div wire:ignore x-data="dashboardBarChart(@js($this->chartMunicipios))"
                        x-init="initChart()" x-effect="updateChart(@js($this->chartMunicipios))">
                        <canvas x-ref="barChart" style="max-height: 300px;"></canvas>
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 text-base-content/40">
                        <p>No hay datos disponibles</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Gráfica: Revisiones por Acción (solo Jefe Financiero) --}}
        @if ($this->user->isJefeFinanciero())
            <div class="bg-base-100 rounded-box shadow-sm border border-base-300 p-5">
                <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-warning" />
                    Revisiones por Tipo de Acción
                </h3>
                @if (array_sum($this->chartRevisiones['data']) > 0)
                    <div class="flex justify-center" wire:ignore
                        x-data="dashboardDonutChart(@js($this->chartRevisiones))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartRevisiones))">
                        <canvas x-ref="donutChart" style="max-height: 280px;"></canvas>
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 text-base-content/40">
                        <p>No hay revisiones registradas</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Gráfica: Expedientes por Municipio (Técnico — sus municipios) --}}
        @if ($this->user->isTecnico() && count($this->chartMunicipios['data']) > 0)
            <div class="bg-base-100 rounded-box shadow-sm border border-base-300 p-5">
                <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                    <x-heroicon-o-building-library class="w-5 h-5 text-accent" />
                    Expedientes en Mis Municipios
                </h3>
                <div wire:ignore x-data="dashboardBarChart(@js($this->chartMunicipios))"
                    x-init="initChart()" x-effect="updateChart(@js($this->chartMunicipios))">
                    <canvas x-ref="barChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        @endif
    </div>

    {{-- Sección inferior: Actividad + Expedientes recientes --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Actividad Reciente (Bitácora) --}}
        @if ($this->user->hasGlobalAccess())
            <div class="bg-base-100 rounded-box shadow-sm border border-base-300">
                <div class="p-4 border-b border-base-300 flex items-center justify-between">
                    <h3 class="font-bold text-sm flex items-center gap-2">
                        <x-heroicon-o-bell-alert class="w-5 h-5 text-info" />
                        Actividad Reciente
                    </h3>
                    @if ($this->user->isAdmin() || $this->user->isDirector())
                        <a href="{{ route('admin.bitacora') }}" wire:navigate
                            class="btn btn-ghost btn-xs gap-1">
                            Ver todo
                            <x-heroicon-o-arrow-right class="w-3 h-3" />
                        </a>
                    @endif
                </div>
                <div class="divide-y divide-base-300 max-h-96 overflow-y-auto">
                    @forelse ($this->actividadReciente as $actividad)
                        <div class="p-3 hover:bg-base-200/50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="avatar placeholder shrink-0 mt-0.5">
                                    <div class="bg-neutral text-neutral-content rounded-full w-7 h-7">
                                        <span
                                            class="text-xs">{{ $actividad->user?->iniciales ?? 'S' }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm leading-snug">
                                        <span
                                            class="font-medium">{{ $actividad->user?->nombre_completo ?? 'Sistema' }}</span>
                                        <span class="text-base-content/60">— {{ $actividad->detalle }}</span>
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="badge badge-xs {{ $actividad->entidad_badge_class }} badge-outline">{{ $actividad->entidad }}</span>
                                        <span
                                            class="badge badge-xs {{ $actividad->tipo_badge_class }}">{{ $actividad->tipo }}</span>
                                        <span
                                            class="text-xs text-base-content/40">{{ $actividad->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-base-content/40">
                            <x-heroicon-o-clock class="w-8 h-8 mx-auto mb-2" />
                            <p class="text-sm">No hay actividad reciente</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Expedientes Recientes --}}
        <div class="bg-base-100 rounded-box shadow-sm border border-base-300">
            <div class="p-4 border-b border-base-300 flex items-center justify-between">
                <h3 class="font-bold text-sm flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary" />
                    Expedientes Recientes
                </h3>
                <a href="{{ route('expedientes.index') }}" wire:navigate class="btn btn-ghost btn-xs gap-1">
                    Ver todos
                    <x-heroicon-o-arrow-right class="w-3 h-3" />
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th>Código SNIP</th>
                            <th>Proyecto</th>
                            <th>Municipio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->expedientesRecientes as $exp)
                            <tr class="hover cursor-pointer"
                                wire:click="$dispatch('navigate', { url: '{{ route('expedientes.show', $exp) }}' })"
                                onclick="window.location='{{ route('expedientes.show', $exp) }}'">
                                <td class="font-mono text-xs">{{ $exp->codigo_snip }}</td>
                                <td>
                                    <p class="text-sm max-w-xs truncate">{{ $exp->nombre_proyecto }}</p>
                                </td>
                                <td class="text-sm">{{ $exp->municipio?->nombre ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $exp->estado_badge_class }}">
                                        {{ $exp->estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-base-content/40">
                                    <p class="text-sm">No hay expedientes</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Chart.js Alpine.data registrations --}}
    @script
        <script>
            // ---- Donut / Pie Chart ----
            Alpine.data('dashboardDonutChart', (initialData) => {
                let chart = null;
                return {
                    initChart() {
                        this.destroyChart();
                        const ctx = this.$refs.donutChart.getContext('2d');
                        chart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: initialData.labels,
                                datasets: [{
                                    data: initialData.data,
                                    backgroundColor: initialData.colors,
                                    borderWidth: 2,
                                    borderColor: '#ffffff',
                                    hoverOffset: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                cutout: '55%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 15,
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            font: { size: 11 }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => {
                                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    },
                    updateChart(newData) {
                        if (!chart) return;
                        chart.data.labels = newData.labels;
                        chart.data.datasets[0].data = newData.data;
                        chart.data.datasets[0].backgroundColor = newData.colors;
                        chart.update();
                    },
                    destroyChart() {
                        if (chart) { chart.destroy(); chart = null; }
                    }
                };
            });

            // ---- Tendencia Mensual (Líneas) ----
            Alpine.data('dashboardTendenciaChart', (initialData) => {
                let chart = null;
                return {
                    initChart() {
                        this.destroyChart();
                        const ctx = this.$refs.tendenciaChart.getContext('2d');
                        chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: initialData.labels,
                                datasets: [
                                    {
                                        label: 'Recibidos',
                                        data: initialData.recibidos,
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.3,
                                        fill: true,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                    },
                                    {
                                        label: 'Aprobados',
                                        data: initialData.aprobadosMes,
                                        borderColor: '#22c55e',
                                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                        tension: 0.3,
                                        fill: true,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                interaction: { intersect: false, mode: 'index' },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { stepSize: 1, font: { size: 11 } },
                                        grid: { color: 'rgba(0,0,0,0.05)' }
                                    },
                                    x: {
                                        ticks: { font: { size: 10 } },
                                        grid: { display: false }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { padding: 15, usePointStyle: true, font: { size: 11 } }
                                    }
                                }
                            }
                        });
                    },
                    updateChart(newData) {
                        if (!chart) return;
                        chart.data.labels = newData.labels;
                        chart.data.datasets[0].data = newData.recibidos;
                        chart.data.datasets[1].data = newData.aprobadosMes;
                        chart.update();
                    },
                    destroyChart() {
                        if (chart) { chart.destroy(); chart = null; }
                    }
                };
            });

            // ---- Bar Chart (horizontal) ----
            Alpine.data('dashboardBarChart', (initialData) => {
                let chart = null;
                return {
                    initChart() {
                        this.destroyChart();
                        const ctx = this.$refs.barChart.getContext('2d');
                        chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: initialData.labels,
                                datasets: [{
                                    label: 'Expedientes',
                                    data: initialData.data,
                                    backgroundColor: '#3b82f6',
                                    borderColor: '#2563eb',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barThickness: 20,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                indexAxis: 'y',
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        ticks: { stepSize: 1, font: { size: 11 } },
                                        grid: { color: 'rgba(0,0,0,0.05)' }
                                    },
                                    y: {
                                        ticks: { font: { size: 10 } },
                                        grid: { display: false }
                                    }
                                },
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        });
                    },
                    updateChart(newData) {
                        if (!chart) return;
                        chart.data.labels = newData.labels;
                        chart.data.datasets[0].data = newData.data;
                        chart.update();
                    },
                    destroyChart() {
                        if (chart) { chart.destroy(); chart = null; }
                    }
                };
            });
        </script>
    @endscript
</div>
