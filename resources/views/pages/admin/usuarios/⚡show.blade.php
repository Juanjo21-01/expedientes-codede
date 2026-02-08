<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\User;
use App\Models\Expediente;
use Carbon\Carbon;

new #[Title('- Detalle Usuario')] class extends Component {
    public User $usuario;

    // Montar el componente con el usuario
    public function mount(User $usuario)
    {
        $this->usuario = $usuario->load(['role', 'municipios', 'expedientes.municipio', 'expedientes.tipoSolicitud']);
    }

    // Refrescar cuando se edita el usuario
    #[On('usuario-guardado')]
    public function refrescar()
    {
        $this->usuario = $this->usuario->fresh(['role', 'municipios', 'expedientes.municipio', 'expedientes.tipoSolicitud']);
    }

    // Cambiar estado del usuario
    public function cambiarEstado()
    {
        // No permitir cambiar estado del Administrador
        if ($this->usuario->isAdmin()) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No puedes cambiar el estado de un Administrador.');
            return;
        }

        // Cambiar estado
        if ($this->usuario->estaActivo()) {
            $this->usuario->desactivar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Usuario desactivado correctamente.');
        } else {
            $this->usuario->activar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Usuario activado correctamente.');
        }

        $this->usuario = $this->usuario->fresh(['role', 'municipios']);
    }

    // Emitir evento para editar
    public function editar()
    {
        $this->dispatch('abrir-modal-usuario', usuarioId: $this->usuario->id);
    }

    // Estadísticas del usuario
    public function getEstadisticasProperty(): array
    {
        $expedientes = $this->usuario->expedientes;

        return [
            'total_expedientes' => $expedientes->count(),
            'expedientes_aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'expedientes_pendientes' => $expedientes->whereIn('estado', [Expediente::ESTADO_RECIBIDO, Expediente::ESTADO_EN_REVISION, Expediente::ESTADO_COMPLETO, Expediente::ESTADO_INCOMPLETO])->count(),
            'expedientes_rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
        ];
    }

    // Últimos expedientes del usuario
    public function getUltimosExpedientesProperty()
    {
        return $this->usuario
            ->expedientes()
            ->with(['municipio', 'tipoSolicitud'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    // Datos para la gráfica de expedientes por mes
    public function getChartDataProperty(): array
    {
        $expedientesPorMes = $this->usuario
            ->expedientes()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, COUNT(*) as cantidad')
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('mes')
            ->pluck('cantidad', 'mes')
            ->toArray();

        $expedientesData = [];
        $mesesDato = collect();

        for ($i = 5; $i >= 0; $i--) {
            $mesesDato->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        foreach ($mesesDato as $mes) {
            $expedientesData[] = $expedientesPorMes[$mes] ?? 0;
        }

        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $meses->push(Carbon::now()->subMonths($i)->locale('es')->isoFormat('MMM YYYY'));
        }

        return [
            'labels' => $meses->toArray(),
            'data' => $expedientesData,
        ];
    }
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs de navegación --}}
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
                <a href="{{ route('admin.usuarios.index') }}" wire:navigate
                    class="gap-1 text-base-content/60 hover:text-primary transition-colors">
                    <x-heroicon-o-users class="w-4 h-4" />
                    Usuarios
                </a>
            </li>
            <li>
                <span class="inline-flex items-center gap-1 text-primary">
                    <x-heroicon-o-user class="w-4 h-4" />
                    {{ $usuario->nombre_completo }}
                </span>
            </li>
        </ul>
    </div>

    {{-- Tarjeta de Perfil Principal --}}
    <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-xl border border-base-300 overflow-hidden">
        {{-- Header decorativo --}}
        <div class="h-24 bg-gradient-to-r from-primary/20 via-secondary/20 to-accent/20"></div>

        <div class="card-body -mt-16">
            {{-- Encabezado con avatar y nombre --}}
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Avatar con indicador de estado --}}
                <div class="indicator">
                    <span class="indicator-item indicator-bottom indicator-end">
                        <div class="inline-grid *:[grid-area:1/1]">
                            @if ($usuario->estaActivo())
                                <div class="status status-success animate-ping"></div>
                                <div class="status status-success"></div>
                            @else
                                <div class="status status-error"></div>
                            @endif
                        </div>
                    </span>
                    <div class="avatar placeholder">
                        <div
                            class="bg-primary text-primary-content rounded-full w-28 h-28 ring-4 ring-base-100 shadow-lg flex justify-center items-center">
                            <span class="text-4xl font-bold">{{ $usuario->iniciales }}</span>
                        </div>
                    </div>
                </div>

                {{-- Información principal --}}
                <div class="flex-1 text-center sm:text-left">
                    <h1 class="text-3xl font-bold tracking-tight">{{ $usuario->nombre_completo }}</h1>
                    @if ($usuario->cargo)
                        <p class="text-base-content/70 text-lg">{{ $usuario->cargo }}</p>
                    @endif
                    <div class="flex items-center justify-center sm:justify-start gap-2 mt-1">
                        <x-heroicon-o-envelope class="w-4 h-4 text-base-content/50" />
                        <span class="text-sm text-base-content/60">{{ $usuario->email }}</span>
                    </div>

                    {{-- Badges de rol y estado --}}
                    <div class="flex flex-wrap gap-2 mt-4 justify-center sm:justify-start">
                        <div
                            class="badge badge-lg gap-2
                            @if ($usuario->isAdmin()) badge-secondary
                            @elseif($usuario->isDirector()) badge-primary
                            @elseif($usuario->isJefeFinanciero()) badge-warning
                            @elseif($usuario->isTecnico()) badge-info
                            @else badge-ghost @endif">
                            <x-heroicon-o-check-badge class="w-4 h-4" />
                            {{ $usuario->role->nombre }}
                        </div>
                        <div
                            class="badge badge-lg gap-2 {{ $usuario->estaActivo() ? 'badge-success' : 'badge-error' }}">
                            <div class="inline-grid *:[grid-area:1/1]">
                                @if ($usuario->estaActivo())
                                    <div class="status status-success animate-ping"></div>
                                    <div class="status status-success"></div>
                                @else
                                    <div class="status status-error"></div>
                                @endif
                            </div>
                            {{ $usuario->estaActivo() ? 'Activo' : 'Inactivo' }}
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row gap-2">
                    @if (!$usuario->isAdmin())
                        <button wire:click="cambiarEstado"
                            class="btn {{ $usuario->estaActivo() ? 'btn-outline btn-error' : 'btn-success' }} btn-sm gap-2">
                            @if ($usuario->estaActivo())
                                <x-heroicon-o-no-symbol class="w-4 h-4" />
                                Desactivar
                            @else
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                Activar
                            @endif
                        </button>
                    @endif
                    <button wire:click="editar" class="btn btn-primary btn-sm gap-2">
                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                        Editar perfil
                    </button>
                </div>
            </div>

            <div class="divider my-4"></div>

            {{-- Detalles del usuario en grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Teléfono --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 text-primary rounded-btn p-3">
                            <x-heroicon-o-phone class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Teléfono</p>
                            <p class="font-semibold">{{ $usuario->telefono ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Fecha de registro --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-info/10 text-info rounded-btn p-3">
                            <x-heroicon-o-calendar class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Fecha de registro</p>
                            <p class="font-semibold">{{ $usuario->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Último acceso --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-success/10 text-success rounded-btn p-3">
                            <x-heroicon-o-clock class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Última actualización</p>
                            <p class="font-semibold">{{ $usuario->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                {{-- 2FA --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-btn p-3 {{ $usuario->two_factor_secret ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                            <x-heroicon-o-shield-check class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Autenticación 2FA</p>
                            <p
                                class="font-semibold {{ $usuario->two_factor_secret ? 'text-success' : 'text-warning' }}">
                                {{ $usuario->two_factor_secret ? 'Habilitada' : 'Deshabilitada' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Municipios asignados --}}
    @if ($usuario->municipios->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-lg gap-3">
                    <div class="bg-accent/10 text-accent rounded-btn p-2">
                        <x-heroicon-o-map-pin class="w-5 h-5" />
                    </div>
                    Municipios Asignados
                    <span class="badge badge-neutral badge-sm">{{ $usuario->municipios->count() }}</span>
                </h2>
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach ($usuario->municipios as $municipio)
                        <a href="{{ route('admin.municipios.show', $municipio->id) }}" wire:navigate
                            class="badge badge-outline badge-lg gap-4 hover:badge-primary transition-colors cursor-pointer flex justify-between align-content-center"
                            title="Ver Municipio">
                            <x-heroicon-o-map-pin class="w-4 h-4" />
                            <span class="">
                                {{ $municipio->nombre }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Estadísticas (solo para técnicos o usuarios con expedientes) --}}
    @if ($usuario->isTecnico() || $this->estadisticas['total_expedientes'] > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total Expedientes --}}
            <div
                class="stat bg-gradient-to-br from-primary/5 to-primary/10 rounded-box shadow border border-primary/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-primary opacity-80">
                    <x-heroicon-o-folder-open class="w-10 h-10" />
                </div>
                <div class="stat-title text-primary/70">Total Expedientes</div>
                <div class="stat-value text-primary">{{ $this->estadisticas['total_expedientes'] }}</div>
                <div class="stat-desc">Registrados</div>
            </div>

            {{-- Aprobados --}}
            <div
                class="stat bg-gradient-to-br from-success/5 to-success/10 rounded-box shadow border border-success/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-success opacity-80">
                    <x-heroicon-o-check-circle class="w-10 h-10" />
                </div>
                <div class="stat-title text-success/70">Aprobados</div>
                <div class="stat-value text-success">{{ $this->estadisticas['expedientes_aprobados'] }}</div>
                <div class="stat-desc">Finalizados</div>
            </div>

            {{-- Pendientes --}}
            <div
                class="stat bg-gradient-to-br from-warning/5 to-warning/10 rounded-box shadow border border-warning/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-warning opacity-80">
                    <x-heroicon-o-clock class="w-10 h-10" />
                </div>
                <div class="stat-title text-warning/70">Pendientes</div>
                <div class="stat-value text-warning">{{ $this->estadisticas['expedientes_pendientes'] }}</div>
                <div class="stat-desc">En proceso</div>
            </div>

            {{-- Rechazados --}}
            <div
                class="stat bg-gradient-to-br from-error/5 to-error/10 rounded-box shadow border border-error/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-error opacity-80">
                    <x-heroicon-o-x-circle class="w-10 h-10" />
                </div>
                <div class="stat-title text-error/70">Rechazados</div>
                <div class="stat-value text-error">{{ $this->estadisticas['expedientes_rechazados'] }}</div>
                <div class="stat-desc">No aprobados</div>
            </div>
        </div>

        {{-- Gráfica de expedientes por mes --}}
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-lg gap-3">
                    <div class="bg-secondary/10 text-secondary rounded-btn p-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                    </div>
                    Expedientes por Mes
                    <span class="badge badge-ghost badge-sm font-normal">Últimos 6 meses</span>
                </h2>
                <div class="w-full h-72 mt-2" x-data="chartComponent(@js($this->chartData))" x-init="initChart()">
                    <canvas x-ref="chart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        {{-- Últimos expedientes --}}
        @if ($this->ultimosExpedientes->isNotEmpty())
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 class="card-title text-lg gap-3">
                            <div class="bg-info/10 text-info rounded-btn p-2">
                                <x-heroicon-o-document-text class="w-5 h-5" />
                            </div>
                            Últimos Expedientes
                        </h2>
                        <a href="{{ route('expedientes.index') }}" wire:navigate
                            class="btn btn-primary btn-sm btn-outline gap-1">
                            Ver todos
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>Código SNIP</th>
                                    <th>Proyecto</th>
                                    <th>Municipio</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->ultimosExpedientes as $expediente)
                                    <tr class="hover:bg-base-200/50 transition-colors">
                                        <td>
                                            <span
                                                class="font-mono font-bold text-primary">{{ $expediente->codigo_snip }}</span>
                                        </td>
                                        <td>
                                            <div class="tooltip tooltip-right"
                                                data-tip="{{ $expediente->nombre_proyecto }}">
                                                <div class="max-w-xs truncate font-medium">
                                                    {{ $expediente->nombre_proyecto }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-ghost badge-sm">{{ $expediente->municipio->nombre }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="text-sm text-base-content/70">{{ $expediente->tipoSolicitud->nombre ?? '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge badge-sm gap-1
                                                @if ($expediente->estado === 'Aprobado') badge-success
                                                @elseif($expediente->estado === 'Rechazado') badge-error
                                                @elseif($expediente->estado === 'En Revisión') badge-warning
                                                @elseif($expediente->estado === 'Recibido') badge-info
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
                                                class="text-sm text-base-content/60">{{ $expediente->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="tooltip" data-tip="Ver expediente">
                                                <a href="{{ route('expedientes.show', $expediente->id) }}"
                                                    wire:navigate class="btn btn-ghost btn-xs btn-circle">
                                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Modal Editar Usuario --}}
    <livewire:modals.usuario-modal />
</div>

{{-- Script para Chart.js con colores dinámicos del tema --}}
@script
    <script>
        Alpine.data('chartComponent', (chartData) => ({
            chart: null,
            chartData: chartData,

            initChart() {
                const ctx = this.$refs.chart.getContext('2d');

                // Obtener colores del tema DaisyUI
                const computedStyle = getComputedStyle(document.documentElement);
                const primaryColor = computedStyle.getPropertyValue('--p') ?
                    `oklch(${computedStyle.getPropertyValue('--p')})` : 'rgb(59, 130, 246)';
                const baseContent = computedStyle.getPropertyValue('--bc') ?
                    `oklch(${computedStyle.getPropertyValue('--bc')})` : 'rgb(100, 116, 139)';

                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.chartData.labels,
                        datasets: [{
                            label: 'Expedientes',
                            data: this.chartData.data,
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
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.parsed.y} expediente${context.parsed.y !== 1 ? 's' : ''}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }
        }));
    </script>
@endscript
