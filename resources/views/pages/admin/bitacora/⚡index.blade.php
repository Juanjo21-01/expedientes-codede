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

    public function mount(): void
    {
        // Últimos 30 días por defecto
        $this->fecha_desde = now()->subDays(30)->format('Y-m-d');
        $this->fecha_hasta = now()->format('Y-m-d');
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
        return [
            'total' => Bitacora::count(),
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

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'entidad', 'tipo', 'usuario_id']);
        $this->fecha_desde = now()->subDays(30)->format('Y-m-d');
        $this->fecha_hasta = now()->format('Y-m-d');
    }

    public function exportarPdf(): void
    {
        $registros = $this->baseQuery()->limit(500)->get();

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
            'totalRegistros' => $registros->count(),
            'generadoPor' => Auth::user()->nombre_completo ?? Auth::user()->nombres,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.bitacoras.listado', $data)->setPaper('letter', 'landscape');

        $filename = "Bitacora_{$this->fecha_desde}_a_{$this->fecha_hasta}.pdf";

        $this->dispatch('descargar-pdf', [
            'contenido' => base64_encode($pdf->output()),
            'nombre' => $filename,
        ]);

        // Registrar la exportación en la bitácora
        Bitacora::registrarReporte("Bitácora exportada a PDF – Período: {$periodoTexto}, Filtros: {$filtrosActivos}, Registros: {$registros->count()}", Bitacora::ENTIDAD_AUDITORIA);
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
        <button wire:click="exportarPdf" class="btn btn-primary btn-sm gap-1" wire:loading.attr="disabled"
            wire:target="exportarPdf">
            <span wire:loading.remove wire:target="exportarPdf">
                <x-heroicon-o-document-arrow-down class="w-4 h-4" />
            </span>
            <span wire:loading wire:target="exportarPdf" class="loading loading-spinner loading-xs"></span>
            Exportar PDF
        </button>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-box shadow-sm border border-base-300 p-4">
            <div class="stat-title text-xs">Total Registros</div>
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
    <div class="bg-base-100 rounded-box shadow-sm border border-base-300 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            {{-- Búsqueda --}}
            <div class="lg:col-span-2">
                <label class="input input-bordered input-sm flex items-center gap-2 w-full">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar en detalle..."
                        class="grow" />
                </label>
            </div>

            {{-- Entidad --}}
            <select wire:model.live="entidad" class="select select-bordered select-sm w-full">
                <option value="">Todas las entidades</option>
                @foreach (\App\Models\Bitacora::getEntidades() as $ent)
                    <option value="{{ $ent }}">{{ $ent }}</option>
                @endforeach
            </select>

            {{-- Tipo --}}
            <select wire:model.live="tipo" class="select select-bordered select-sm w-full">
                <option value="">Todos los tipos</option>
                @foreach (\App\Models\Bitacora::getTipos() as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>

            {{-- Usuario --}}
            <select wire:model.live="usuario_id" class="select select-bordered select-sm w-full">
                <option value="">Todos los usuarios</option>
                @foreach ($this->usuarios as $u)
                    <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                @endforeach
            </select>

            {{-- Limpiar --}}
            <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm gap-1">
                <x-heroicon-o-x-circle class="w-4 h-4" />
                Limpiar
            </button>
        </div>

        {{-- Rango de fechas --}}
        <div class="flex flex-wrap items-center gap-3 mt-3 pt-3 border-t border-base-300">
            <span class="text-sm font-medium text-base-content/70">Período:</span>
            <input type="date" wire:model.live="fecha_desde" class="input input-bordered input-sm" />
            <span class="text-sm text-base-content/50">al</span>
            <input type="date" wire:model.live="fecha_hasta" class="input input-bordered input-sm" />
        </div>
    </div>

    {{-- Tabla --}}
    <livewire:table.bitacora-table :search="$search" :entidad="$entidad" :tipo="$tipo" :usuario_id="$usuario_id" :fecha_desde="$fecha_desde"
        :fecha_hasta="$fecha_hasta" />

    {{-- Modal de detalle --}}
    <livewire:modals.bitacora-detalle-modal />
</div>

@script
    <script>
        Livewire.on('descargar-pdf', ([data]) => {
            const link = document.createElement('a');
            link.href = 'data:application/pdf;base64,' + data.contenido;
            link.download = data.nombre;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
@endscript
