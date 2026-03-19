<?php

use App\Models\Bitacora;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title(' - Bitácora')] class extends Component {
    // ---- Filtros ----
    public string $search = '';
    public string $entidad = '';
    public string $tipo = '';
    public string $usuario_id = '';
    public string $fecha_desde = '';
    public string $fecha_hasta = '';

    // ---- Filtros de formulario (se aplican con botón) ----
    public string $filtro_search = '';
    public string $filtro_entidad = '';
    public string $filtro_tipo = '';
    public string $filtro_usuario_id = '';
    public string $filtro_periodo = 'mes_actual';
    public string $filtro_fecha_desde = '';
    public string $filtro_fecha_hasta = '';

    public function mount(): void
    {
        [$desde, $hasta] = $this->fechasPeriodo($this->filtro_periodo);
        $this->filtro_fecha_desde = $desde;
        $this->filtro_fecha_hasta = $hasta;

        $this->aplicarFiltros();
    }

    private function fechasPeriodo(string $periodo): array
    {
        return match ($periodo) {
            'mes_actual' => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'este_anio' => [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default => [$this->filtro_fecha_desde, $this->filtro_fecha_hasta],
        };
    }

    public function updatedFiltroPeriodo(): void
    {
        if ($this->filtro_periodo !== 'personalizado') {
            [$this->filtro_fecha_desde, $this->filtro_fecha_hasta] = $this->fechasPeriodo($this->filtro_periodo);
        }
    }

    // ---- Query base con filtros ----
    private function baseQuery()
    {
        return Bitacora::query()
            ->with('user')
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->entidad, fn($q) => $q->deEntidad($this->entidad))
            ->when($this->tipo, fn($q) => $q->deTipo($this->tipo))
            ->when($this->usuario_id, fn($q) => $q->deUsuario((int) $this->usuario_id))
            ->when($this->fecha_desde && $this->fecha_hasta, fn($q) => $q->entreFechas($this->fecha_desde, $this->fecha_hasta))
            ->recientes();
    }

    // ---- Datos computados ----

    #[Computed]
    public function estadisticas(): array
    {
        $totalFiltrado = (clone $this->baseQuery())->count();

        return [
            'total' => $totalFiltrado,
            'hoy' => Bitacora::deHoy()->count(),
            'este_mes' => Bitacora::deEsteMes()->count(),
            'usuarios_activos' => Bitacora::deEsteMes()->distinct('user_id')->count('user_id'),
        ];
    }

    #[Computed]
    public function usuarios()
    {
        return User::whereHas('bitacoras')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos']);
    }

    // ---- Acciones ----

    public function aplicarFiltros(): void
    {
        if ($this->filtro_periodo !== 'personalizado') {
            [$this->filtro_fecha_desde, $this->filtro_fecha_hasta] = $this->fechasPeriodo($this->filtro_periodo);
        }

        if ($this->filtro_fecha_desde && $this->filtro_fecha_hasta && $this->filtro_fecha_desde > $this->filtro_fecha_hasta) {
            [$this->filtro_fecha_desde, $this->filtro_fecha_hasta] = [$this->filtro_fecha_hasta, $this->filtro_fecha_desde];
        }

        $this->search = trim($this->filtro_search);
        $this->entidad = $this->filtro_entidad;
        $this->tipo = $this->filtro_tipo;
        $this->usuario_id = $this->filtro_usuario_id;
        $this->fecha_desde = $this->filtro_fecha_desde;
        $this->fecha_hasta = $this->filtro_fecha_hasta;

        unset($this->estadisticas);
    }

    public function limpiarFiltros(): void
    {
        $this->filtro_search = '';
        $this->filtro_entidad = '';
        $this->filtro_tipo = '';
        $this->filtro_usuario_id = '';
        $this->filtro_periodo = 'mes_actual';

        [$this->filtro_fecha_desde, $this->filtro_fecha_hasta] = $this->fechasPeriodo($this->filtro_periodo);

        $this->aplicarFiltros();
    }

    public function exportarPdf(): void
    {
        $baseQuery = $this->baseQuery();
        $limiteRegistros = 500;
        $totalRegistros = (clone $baseQuery)->count();
        $registros = (clone $baseQuery)->limit($limiteRegistros)->get();
        $totalImpresos = $registros->count();

        $periodoTexto = Carbon::parse($this->fecha_desde)->format('d/m/Y') . ' al ' . Carbon::parse($this->fecha_hasta)->format('d/m/Y');

        $filtrosActivos =
            collect([$this->entidad ? "Entidad: {$this->entidad}" : null, $this->tipo ? "Tipo: {$this->tipo}" : null, $this->usuario_id ? 'Usuario: ' . (User::find($this->usuario_id)?->nombre_completo ?? 'N/A') : null, $this->search ? "Búsqueda: {$this->search}" : null])
                ->filter()
                ->implode(' | ') ?:
            'Ninguno';

        $data = [
            'registros' => $registros,
            'periodoTexto' => $periodoTexto,
            'filtrosActivos' => $filtrosActivos,
            'totalRegistros' => $totalRegistros,
            'totalImpresos' => $totalImpresos,
            'limiteRegistros' => $limiteRegistros,
            'generadoPor' => Auth::user()->nombre_completo ?? Auth::user()->nombres,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.bitacoras.listado', $data)->setPaper('letter', 'landscape');

        $filename = "Bitacora_{$this->fecha_desde}_a_{$this->fecha_hasta}.pdf";

        $this->dispatch('descargar-pdf-bitacora', [
            'contenido' => base64_encode($pdf->output()),
            'nombre' => $filename,
        ]);

        // Registrar la exportación en la bitácora
        Bitacora::registrarReporte("Bitácora exportada a PDF – Período: {$periodoTexto}, Filtros: {$filtrosActivos}, Registros filtrados: {$totalRegistros}, Registros impresos: {$totalImpresos}", Bitacora::ENTIDAD_AUDITORIA);
    }
};
?>

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Bitácora de Actividades</h1>
            <p class="text-base-content/60 text-sm mt-1">Registro de auditoría del sistema</p>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-box shadow-sm border border-base-300 p-4">
            <div class="stat-title text-xs">Total Registros (Filtros)</div>
            <div class="stat-value text-2xl text-primary">{{ number_format($this->estadisticas['total']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm border border-base-300 p-4">
            <div class="stat-title text-xs">Hoy</div>
            <div class="stat-value text-2xl text-success">{{ $this->estadisticas['hoy'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm border border-base-300 p-4">
            <div class="stat-title text-xs">Este Mes</div>
            <div class="stat-value text-2xl text-info">{{ $this->estadisticas['este_mes'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm border border-base-300 p-4">
            <div class="stat-title text-xs">Usuarios Activos (Mes)</div>
            <div class="stat-value text-2xl text-warning">{{ $this->estadisticas['usuarios_activos'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card bg-base-100 shadow-sm border border-base-300 mb-6">
        <div class="card-body p-4">
            <div class="flex flex-wrap items-end gap-3">
                {{-- Período --}}
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Período</span>
                    </label>
                    <select wire:model.live="filtro_periodo" class="select select-bordered select-sm w-full sm:w-48">
                        <option value="mes_actual">Mes Actual</option>
                        <option value="este_anio">Este Año</option>
                        <option value="personalizado">Rango Personalizado</option>
                    </select>
                </div>

                {{-- Fecha Desde --}}
                @if ($filtro_periodo === 'personalizado')
                    <div class="form-control w-full sm:w-auto">
                        <label class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wider">Desde</span>
                        </label>
                        <input type="date" wire:model.defer="filtro_fecha_desde" class="input input-bordered input-sm w-full sm:w-44" />
                    </div>

                    {{-- Fecha Hasta --}}
                    <div class="form-control w-full sm:w-auto">
                        <label class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wider">Hasta</span>
                        </label>
                        <input type="date" wire:model.defer="filtro_fecha_hasta" class="input input-bordered input-sm w-full sm:w-44" />
                    </div>
                @endif

                {{-- Búsqueda --}}
                <div class="form-control w-full sm:w-auto sm:flex-1 min-w-55">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Buscar</span>
                    </label>
                    <label class="input input-bordered input-sm flex items-center gap-2 w-full">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                        <input type="text" wire:model.defer="filtro_search" wire:keydown.enter="aplicarFiltros" placeholder="Buscar en detalle..." class="grow" />
                    </label>
                </div>

                {{-- Entidad --}}
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Entidad</span>
                    </label>
                    <select wire:model.defer="filtro_entidad" class="select select-bordered select-sm w-full sm:w-44">
                        <option value="">Todas las entidades</option>
                        @foreach (Bitacora::getEntidades() as $ent)
                            <option value="{{ $ent }}">{{ $ent }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo --}}
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Tipo</span>
                    </label>
                    <select wire:model.defer="filtro_tipo" class="select select-bordered select-sm w-full sm:w-44">
                        <option value="">Todos los tipos</option>
                        @foreach (Bitacora::getTipos() as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Usuario --}}
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Usuario</span>
                    </label>
                    <select wire:model.defer="filtro_usuario_id" class="select select-bordered select-sm w-full sm:w-52">
                        <option value="">Todos los usuarios</option>
                        @foreach ($this->usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Botones --}}
                <div class="flex gap-2 ml-auto w-full sm:w-auto">
                    <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm gap-1">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        Limpiar
                    </button>
                    <button wire:click="aplicarFiltros" class="btn btn-outline btn-sm gap-1" wire:loading.attr="disabled" wire:target="aplicarFiltros">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        Buscar
                    </button>
                    <button wire:click="exportarPdf" class="btn btn-primary btn-sm gap-1" wire:loading.attr="disabled" wire:target="exportarPdf">
                        <span wire:loading.remove wire:target="exportarPdf">
                            <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                        </span>
                        <span wire:loading wire:target="exportarPdf" class="loading loading-spinner loading-xs"></span>
                        Exportar PDF
                    </button>
                </div>
            </div>

            {{-- Período aplicado --}}
            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-base-300 text-xs text-base-content/50">
                <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                <span>
                    Período aplicado:
                    {{ $fecha_desde ? \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') : '--' }}
                    al
                    {{ $fecha_hasta ? \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') : '--' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <livewire:table.bitacora-table :search="$search" :entidad="$entidad" :tipo="$tipo" :usuario_id="$usuario_id"
        :fecha_desde="$fecha_desde" :fecha_hasta="$fecha_hasta" />

    {{-- Modal de detalle --}}
    <livewire:modals.bitacora-detalle-modal />
</div>

@script
    <script>
        if (!window.__bitacoraPdfListenerRegistered) {
            window.__bitacoraPdfListenerRegistered = true;

            Livewire.on('descargar-pdf-bitacora', ([data]) => {
                const link = document.createElement('a');
                link.href = 'data:application/pdf;base64,' + data.contenido;
                link.download = data.nombre;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    </script>
@endscript
