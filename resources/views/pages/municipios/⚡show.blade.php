<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Municipio;
use App\Models\Expediente;
use App\Models\Role;

new #[Title('- Detalle Municipalidad')] class extends Component {
    public Municipio $municipio;
    public string $anioFiltro = '';

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio->load(['expedientes.responsable']);
        $this->anioFiltro = (string) now()->year;
    }

    #[Computed]
    public function aniosDisponibles()
    {
        $anios = $this->municipio->expedientes()->selectRaw('YEAR(fecha_recibido) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->toArray();

        return empty($anios) ? [now()->year] : $anios;
    }

    #[Computed]
    public function usuarioMunicipal()
    {
        return $this->municipio->users()->whereHas('role', fn($q) => $q->where('nombre', Role::MUNICIPAL))->where('users.estado', true)->first();
    }

    #[Computed]
    public function tecnicosAsignados()
    {
        return $this->municipio->users()->whereHas('role', fn($q) => $q->where('nombre', Role::TECNICO))->where('users.estado', true)->get();
    }

    #[Computed]
    public function estadisticas()
    {
        $expedientes = $this->municipio->expedientes;

        return [
            'total' => $expedientes->count(),
            'recibidos' => $expedientes->where('estado', Expediente::ESTADO_RECIBIDO)->count(),
            'en_revision' => $expedientes->where('estado', Expediente::ESTADO_EN_REVISION)->count(),
            'aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
            'archivados' => $expedientes->where('estado', Expediente::ESTADO_ARCHIVADO)->count(),
        ];
    }

    #[Computed]
    public function estadisticasAnio()
    {
        $expedientes = $this->municipio->expedientes()->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))->get();

        return [
            'total' => $expedientes->count(),
            'recibidos' => $expedientes->where('estado', Expediente::ESTADO_RECIBIDO)->count(),
            'en_revision' => $expedientes->where('estado', Expediente::ESTADO_EN_REVISION)->count(),
            'aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
            'archivados' => $expedientes->where('estado', Expediente::ESTADO_ARCHIVADO)->count(),
        ];
    }

    #[Computed]
    public function expedientesAnio()
    {
        return $this->municipio
            ->expedientes()
            ->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))
            ->with(['responsable'])
            ->orderBy('fecha_recibido', 'desc')
            ->limit(10)
            ->get();
    }

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

        return ['labels' => $labels, 'data' => $data];
    }

    #[Computed]
    public function chartEstados()
    {
        $stats = $this->estadisticasAnio;

        return [
            'labels' => ['Recibidos', 'En Revisión', 'Aprobados', 'Rechazados', 'Archivados'],
            'data' => [$stats['recibidos'], $stats['en_revision'], $stats['aprobados'], $stats['rechazados'], $stats['archivados']],
            'colors' => ['rgba(59, 130, 246, 0.7)', 'rgba(234, 179, 8, 0.7)', 'rgba(16, 185, 129, 0.7)', 'rgba(239, 68, 68, 0.7)', 'rgba(148, 163, 184, 0.7)'],
        ];
    }

    public function updatedAnioFiltro()
    {
        unset($this->estadisticasAnio, $this->expedientesAnio, $this->chartData, $this->chartEstados);
    }
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm font-medium">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="gap-1 text-base-content/60 hover:text-info transition-colors">
                    <x-heroicon-o-home class="w-4 h-4" />
                    Inicio
                </a>
            </li>
            <li>
                <a href="{{ route('municipios.index') }}" wire:navigate
                    class="gap-1 text-base-content/60 hover:text-info transition-colors">
                    <x-heroicon-o-building-library class="w-4 h-4" />
                    Municipalidades
                </a>
            </li>
            <li>
                <span class="inline-flex items-center gap-1 text-info font-semibold">
                    <x-heroicon-o-map-pin class="w-4 h-4" />
                    {{ $municipio->nombre }}
                </span>
            </li>
        </ul>
    </div>

    {{-- Header Card --}}
    <div class="card bg-linear-to-r from-base-100 via-info/5 to-success/10 shadow-sm border border-info/20">
        <div class="card-body p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="bg-info/20 text-info rounded-xl w-14 h-14 flex items-center justify-center">
                            <x-heroicon-o-building-library class="w-7 h-7" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ $municipio->nombre }}</h1>
                        <p class="text-base-content/60 flex items-center gap-1.5">
                            <x-heroicon-o-map-pin class="w-4 h-4" />
                            {{ $municipio->departamento }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if (auth()->user()->isMunicipal())
                        <a href="{{ route('notificaciones.index') }}" wire:navigate
                            class="btn btn-outline btn-info btn-sm gap-2">
                            <x-heroicon-o-envelope-open class="w-4 h-4" />
                            Mis Notificaciones
                        </a>
                    @endif
                    <div
                        class="badge badge-soft badge-lg gap-1.5 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                        <span
                            class="w-2 h-2 rounded-full {{ $municipio->estaActivo() ? 'bg-success-content' : 'bg-error-content' }}"></span>
                        {{ $municipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                    </div>
                </div>
            </div>

            <div class="divider my-2"></div>

            {{-- Datos de contacto --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="flex items-center gap-3 bg-base-100/80 rounded-box px-4 py-3 border border-base-content/10">
                    <div class="bg-info/15 text-info rounded-btn p-2">
                        <x-heroicon-o-user class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-base-content/50 uppercase tracking-wider">Contacto</p>
                        <p class="font-semibold text-sm truncate">{{ $municipio->contacto_nombre ?? 'Sin asignar' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-base-100/80 rounded-box px-4 py-3 border border-base-content/10">
                    <div class="bg-info/10 text-info rounded-btn p-2">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-base-content/50 uppercase tracking-wider">Email</p>
                        <p class="font-semibold text-sm truncate">{{ $municipio->contacto_email ?? 'No registrado' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-base-100/80 rounded-box px-4 py-3 border border-base-content/10">
                    <div class="bg-success/10 text-success rounded-btn p-2">
                        <x-heroicon-o-phone class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-base-content/50 uppercase tracking-wider">Teléfono</p>
                        <p class="font-semibold text-sm truncate">
                            {{ $municipio->contacto_telefono ?? 'No registrado' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats generales --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="stat bg-linear-to-br from-info/5 to-info/10 rounded-box shadow-sm border border-info/20 py-3 px-4">
            <div class="stat-figure text-info">
                <x-heroicon-o-folder-open class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-2xl text-info">{{ $this->estadisticas['total'] }}</div>
            <div class="stat-desc">Expedientes</div>
        </div>
        <div
            class="stat bg-linear-to-br from-success/5 to-success/10 rounded-box shadow-sm border border-success/20 py-3 px-4">
            <div class="stat-figure text-success">
                <x-heroicon-o-check-circle class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Aprobados</div>
            <div class="stat-value text-2xl text-success">{{ $this->estadisticas['aprobados'] }}</div>
            <div class="stat-desc">Finalizados</div>
        </div>
        <div
            class="stat bg-linear-to-br from-warning/5 to-warning/10 rounded-box shadow-sm border border-warning/20 py-3 px-4">
            <div class="stat-figure text-warning">
                <x-heroicon-o-clock class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">En Proceso</div>
            <div class="stat-value text-2xl text-warning">
                {{ $this->estadisticas['recibidos'] + $this->estadisticas['en_revision'] }}
            </div>
            <div class="stat-desc">Activos</div>
        </div>
        <div
            class="stat bg-linear-to-br from-error/5 to-error/10 rounded-box shadow-sm border border-error/20 py-3 px-4">
            <div class="stat-figure text-error">
                <x-heroicon-o-x-circle class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Rechazados</div>
            <div class="stat-value text-2xl text-error">{{ $this->estadisticas['rechazados'] }}</div>
            <div class="stat-desc">No aprobados</div>
        </div>
    </div>

    {{-- Expedientes por Año --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/10">
        <div class="card-body p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="bg-info/15 text-info rounded-btn p-1.5">
                        <x-heroicon-o-calendar-days class="w-5 h-5" />
                    </div>
                    <h2 class="card-title text-lg">Expedientes por Año</h2>
                </div>
                <select wire:model.live="anioFiltro" class="select select-sm select-bordered w-32">
                    @foreach ($this->aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Resumen badges del año --}}
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="badge badge-soft badge-info gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['recibidos'] }}</span> Recibidos
                </span>
                <span class="badge badge-soft badge-warning gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['en_revision'] }}</span> En Revisión
                </span>
                <span class="badge badge-soft badge-success gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['aprobados'] }}</span> Aprobados
                </span>
                <span class="badge badge-soft badge-error gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['rechazados'] }}</span> Rechazados
                </span>
            </div>

            {{-- Gráficas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-5">
                <div class="bg-base-100 rounded-box p-4 border border-base-content/10">
                    <h3 class="font-semibold text-sm mb-3 text-base-content/70 flex items-center gap-1.5">
                        <x-heroicon-o-chart-bar class="w-4 h-4" />
                        Expedientes por Mes — {{ $anioFiltro }}
                    </h3>
                    <div class="w-full h-64" wire:ignore x-data="barChart(@js($this->chartData))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartData))">
                        <canvas x-ref="barChart" class="w-full h-full"></canvas>
                    </div>
                </div>
                <div class="bg-base-100 rounded-box p-4 border border-base-content/10">
                    <h3 class="font-semibold text-sm mb-3 text-base-content/70 flex items-center gap-1.5">
                        <x-heroicon-o-chart-pie class="w-4 h-4" />
                        Distribución por Estado — {{ $anioFiltro }}
                    </h3>
                    <div class="w-full h-64" wire:ignore x-data="pieChart(@js($this->chartEstados))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartEstados))">
                        <canvas x-ref="pieChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Usuarios Asignados --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/10">
        <div class="card-body p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="bg-secondary/10 text-secondary rounded-btn p-1.5">
                    <x-heroicon-o-user-group class="w-5 h-5" />
                </div>
                <h2 class="card-title text-lg">Usuarios Asignados</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Usuario Municipal --}}
                <div class="rounded-box border border-base-300 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-ghost badge-sm">Municipal</span>
                    </div>
                    @if ($this->usuarioMunicipal)
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center">
                                    <span
                                        class="text-sm font-bold">{{ $this->usuarioMunicipal->iniciales ?? 'U' }}</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-sm">{{ $this->usuarioMunicipal->nombre_completo }}</p>
                                <p class="text-xs text-base-content/60 truncate">{{ $this->usuarioMunicipal->email }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-base-content/40">
                            <x-heroicon-o-user-minus class="w-5 h-5" />
                            <p class="italic text-sm">Sin usuario municipal asignado</p>
                        </div>
                    @endif
                </div>

                {{-- Técnicos --}}
                <div class="rounded-box border border-base-300 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-info badge-sm">Técnicos</span>
                        @if ($this->tecnicosAsignados->isNotEmpty())
                            <span class="badge badge-ghost badge-xs">{{ $this->tecnicosAsignados->count() }}</span>
                        @endif
                    </div>
                    @if ($this->tecnicosAsignados->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($this->tecnicosAsignados as $tecnico)
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div
                                            class="bg-info text-info-content rounded-full w-8 h-8 flex items-center justify-center">
                                            <span class="text-xs font-bold">{{ $tecnico->iniciales ?? 'T' }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium">{{ $tecnico->nombre_completo }}</p>
                                        <p class="text-xs text-base-content/50 truncate">{{ $tecnico->email }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-base-content/40">
                            <x-heroicon-o-user-minus class="w-5 h-5" />
                            <p class="italic text-sm">Sin técnicos asignados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Expedientes del año --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/10">
        <div class="card-body p-5">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <div class="bg-accent/10 text-accent rounded-btn p-1.5">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                    </div>
                    <h2 class="card-title text-lg">Expedientes {{ $anioFiltro }}</h2>
                </div>
                @if ($this->expedientesAnio->isNotEmpty())
                    <span class="badge badge-info badge-soft badge-sm">
                        {{ $this->expedientesAnio->count() }}
                        {{ $this->expedientesAnio->count() === 1 ? 'registro' : 'registros' }}
                    </span>
                @endif
            </div>

            @if ($this->expedientesAnio->isNotEmpty())
                <div class="rounded-box border border-base-content/10 overflow-x-auto">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr class="bg-base-200/60 text-xs uppercase tracking-wide text-base-content/70">
                                <th class="font-semibold text-xs uppercase text-base-content/60">No.</th>
                                <th class="font-semibold text-xs uppercase text-base-content/60">SNIP</th>
                                <th class="font-semibold text-xs uppercase text-base-content/60">Proyecto</th>
                                <th class="font-semibold text-xs uppercase text-base-content/60">Estado</th>
                                <th class="font-semibold text-xs uppercase text-base-content/60">Recibido</th>
                                <th class="font-semibold text-xs uppercase text-base-content/60">Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->expedientesAnio as $index => $expediente)
                                <tr class="hover">
                                    <td class="text-base-content/50">{{ $index + 1 }}</td>
                                    <td>
                                        <span
                                            class="font-mono text-info font-semibold">{{ $expediente->codigo_snip }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="max-w-xs truncate block">{{ $expediente->nombre_proyecto }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-soft badge-sm {{ $expediente->estado_badge_class }}">{{ $expediente->estado }}</span>
                                    </td>
                                    <td class="text-sm">{{ $expediente->fecha_recibido?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-sm">{{ $expediente->responsable->nombre_completo ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-base-content/40">
                    <div class="bg-base-200 rounded-full p-3 mb-2">
                        <x-heroicon-o-document-text class="w-8 h-8" />
                    </div>
                    <p class="font-medium text-sm">No hay expedientes para el año {{ $anioFiltro }}</p>
                    <p class="text-xs mt-0.5">Selecciona otro año para ver los registros</p>
                </div>
            @endif
        </div>
    </div>
</div>

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
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            },
            updateChart(newData) {
                if (!this.chart) return;
                this.chart.data.labels = newData.labels;
                this.chart.data.datasets[0].data = newData.data;
                this.chart.update('active');
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
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            },
            updateChart(newData) {
                if (!this.chart) return;
                this.chart.data.labels = newData.labels;
                this.chart.data.datasets[0].data = newData.data;
                this.chart.data.datasets[0].backgroundColor = newData.colors;
                this.chart.update('active');
            }
        }));
    </script>
@endscript
