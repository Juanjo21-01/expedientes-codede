<?php

use App\Models\Expediente;
use App\Models\Municipio;
use App\Models\RevisionFinanciera;
use App\Models\TipoSolicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Title(' - Reportes')] class extends Component {
    // ---- Propiedades de Filtro ----
    public string $tab = 'resumen';
    public string $periodo = 'mes_actual';
    public ?string $fecha_desde = null;
    public ?string $fecha_hasta = null;
    public string $municipio_id = '';

    // ---- Lifecycle ----

    public function mount(): void
    {
        $this->setFechasPeriodo();
    }

    // ---- Watchers ----

    public function updatedPeriodo(): void
    {
        $this->setFechasPeriodo();
        $this->resetComputadas();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetComputadas();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetComputadas();
    }

    public function updatedMunicipioId(): void
    {
        $this->resetComputadas();
    }

    public function updatedTab(): void
    {
        $this->resetComputadas();
    }

    // ---- Helpers de Período ----

    private function setFechasPeriodo(): void
    {
        match ($this->periodo) {
            'mes_actual' => $this->setMesActual(),
            'este_anio' => $this->setEsteAnio(),
            'personalizado' => null, // no cambiar fechas
        };
    }

    private function setMesActual(): void
    {
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    private function setEsteAnio(): void
    {
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfYear()->format('Y-m-d');
    }

    // ---- Query Base con Filtros ----

    private function baseQuery()
    {
        $user = Auth::user();

        return Expediente::query()
            ->accesiblesPor($user)
            ->when($this->fecha_desde && $this->fecha_hasta, fn($q) => $q->recibidosEntre($this->fecha_desde, $this->fecha_hasta))
            ->when($this->municipio_id, fn($q) => $q->deMunicipio((int) $this->municipio_id));
    }

    // ---- Computadas: Municipios Disponibles ----

    #[Computed]
    public function municipiosDisponibles()
    {
        $user = Auth::user();

        if ($user->hasGlobalAccess()) {
            return Municipio::where('estado', true)->orderBy('nombre')->get();
        }

        return Municipio::whereIn('id', $user->municipios_ids)->where('estado', true)->orderBy('nombre')->get();
    }

    // ---- Computadas: Resumen General ----

    #[Computed]
    public function estadisticas()
    {
        $query = $this->baseQuery();

        $total = (clone $query)->count();
        $recibidos = (clone $query)->deEstado(Expediente::ESTADO_RECIBIDO)->count();
        $enRevision = (clone $query)->deEstado(Expediente::ESTADO_EN_REVISION)->count();
        $completos = (clone $query)->deEstado(Expediente::ESTADO_COMPLETO)->count();
        $incompletos = (clone $query)->deEstado(Expediente::ESTADO_INCOMPLETO)->count();
        $aprobados = (clone $query)->deEstado(Expediente::ESTADO_APROBADO)->count();
        $rechazados = (clone $query)->deEstado(Expediente::ESTADO_RECHAZADO)->count();
        $archivados = (clone $query)->deEstado(Expediente::ESTADO_ARCHIVADO)->count();

        $montoContratado = (clone $query)->sum('monto_contrato');
        $montoAprobado = RevisionFinanciera::whereIn('expediente_id', (clone $query)->select('id'))->sum('monto_aprobado');

        $enProceso = $recibidos + $enRevision + $completos + $incompletos;

        return compact('total', 'recibidos', 'enRevision', 'completos', 'incompletos', 'aprobados', 'rechazados', 'archivados', 'enProceso', 'montoContratado', 'montoAprobado');
    }

    // ---- Computadas: Datos por Municipio ----

    #[Computed]
    public function datosPorMunicipio()
    {
        $user = Auth::user();

        $municipiosQuery = Municipio::where('estado', true)->orderBy('nombre');

        if (!$user->hasGlobalAccess()) {
            $municipiosQuery->whereIn('id', $user->municipios_ids);
        }

        if ($this->municipio_id) {
            $municipiosQuery->where('id', $this->municipio_id);
        }

        $municipios = $municipiosQuery->get();

        $datos = $municipios->map(function ($municipio) {
            $query = Expediente::query()
                ->deMunicipio($municipio->id)
                ->when($this->fecha_desde && $this->fecha_hasta, fn($q) => $q->recibidosEntre($this->fecha_desde, $this->fecha_hasta));

            return [
                'nombre' => $municipio->nombre,
                'recibidos' => (clone $query)->deEstado(Expediente::ESTADO_RECIBIDO)->count(),
                'en_revision' => (clone $query)->deEstado(Expediente::ESTADO_EN_REVISION)->count(),
                'completos' => (clone $query)->deEstado(Expediente::ESTADO_COMPLETO)->count(),
                'incompletos' => (clone $query)->deEstado(Expediente::ESTADO_INCOMPLETO)->count(),
                'aprobados' => (clone $query)->deEstado(Expediente::ESTADO_APROBADO)->count(),
                'rechazados' => (clone $query)->deEstado(Expediente::ESTADO_RECHAZADO)->count(),
                'archivados' => (clone $query)->deEstado(Expediente::ESTADO_ARCHIVADO)->count(),
                'total' => (clone $query)->count(),
                'monto_contratado' => (clone $query)->sum('monto_contrato'),
                'monto_aprobado' => RevisionFinanciera::whereIn('expediente_id', (clone $query)->select('id'))->sum('monto_aprobado'),
            ];
        });

        return $datos;
    }

    // ---- Computadas: Datos por Tipo de Solicitud ----

    #[Computed]
    public function datosPorTipo()
    {
        $tipos = TipoSolicitud::ordenados()->get();

        return $tipos->map(function ($tipo) {
            $query = $this->baseQuery()->where('tipo_solicitud_id', $tipo->id);

            $total = (clone $query)->count();
            $aprobados = (clone $query)->deEstado(Expediente::ESTADO_APROBADO)->count();
            $pendientes = (clone $query)->activos()->count();
            $rechazados = (clone $query)->deEstado(Expediente::ESTADO_RECHAZADO)->count();
            $montoContratado = (clone $query)->sum('monto_contrato');
            $montoAprobado = RevisionFinanciera::whereIn('expediente_id', (clone $query)->select('id'))->sum('monto_aprobado');

            return [
                'nombre' => $tipo->nombre,
                'total' => $total,
                'aprobados' => $aprobados,
                'pendientes' => $pendientes,
                'rechazados' => $rechazados,
                'monto_contratado' => $montoContratado,
                'monto_aprobado' => $montoAprobado,
                'porcentaje_aprobacion' => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0,
            ];
        });
    }

    // ---- Computadas: Datos Financieros ----

    #[Computed]
    public function datosFinancieros()
    {
        $expedientes = $this->baseQuery()
            ->with(['municipio', 'tipoSolicitud', 'ultimaRevision'])
            ->whereHas('revisionesFinancieras')
            ->orderBy('fecha_recibido', 'desc')
            ->get();

        return $expedientes->map(function ($exp) {
            $montoAprobado = $exp->ultimaRevision?->monto_aprobado ?? 0;
            $diferencia = $montoAprobado - ($exp->monto_contrato ?? 0);
            $diasTramite = $exp->fecha_aprobacion ? $exp->fecha_recibido->diffInDays($exp->fecha_aprobacion) : $exp->fecha_recibido->diffInDays(now());

            return [
                'id' => $exp->id,
                'codigo_snip' => $exp->codigo_snip,
                'municipio' => $exp->municipio->nombre ?? 'N/A',
                'tipo_solicitud' => $exp->tipoSolicitud->nombre ?? 'N/A',
                'monto_contratado' => $exp->monto_contrato ?? 0,
                'monto_aprobado' => $montoAprobado,
                'diferencia' => $diferencia,
                'dias_tramite' => $diasTramite,
                'estado' => $exp->estado,
                'estado_badge' => $exp->estado_badge_class,
            ];
        });
    }

    // ---- Computadas: Resumen Financiero ----

    #[Computed]
    public function resumenFinanciero()
    {
        $query = $this->baseQuery();

        $montoContratado = (clone $query)->sum('monto_contrato');
        $montoAprobado = RevisionFinanciera::whereIn('expediente_id', (clone $query)->select('id'))->sum('monto_aprobado');
        $diferencia = $montoAprobado - $montoContratado;
        $variacion = $montoContratado > 0 ? round(($diferencia / $montoContratado) * 100, 1) : 0;

        $totalExpedientes = (clone $query)->count();
        $promedioMonto = $totalExpedientes > 0 ? $montoContratado / $totalExpedientes : 0;

        // Promedio días de trámite para aprobados
        $aprobados = (clone $query)->deEstado(Expediente::ESTADO_APROBADO)->whereNotNull('fecha_aprobacion')->get();

        $promedioDias = $aprobados->count() > 0 ? round($aprobados->avg(fn($e) => $e->fecha_recibido->diffInDays($e->fecha_aprobacion))) : 0;

        return compact('montoContratado', 'montoAprobado', 'diferencia', 'variacion', 'promedioMonto', 'promedioDias');
    }

    // ---- Computadas: Gráficas ----

    #[Computed]
    public function chartEstados()
    {
        $stats = $this->estadisticas;

        return [
            'labels' => ['Recibidos', 'En Revisión', 'Completos', 'Incompletos', 'Aprobados', 'Rechazados', 'Archivados'],
            'data' => [$stats['recibidos'], $stats['enRevision'], $stats['completos'], $stats['incompletos'], $stats['aprobados'], $stats['rechazados'], $stats['archivados']],
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

    #[Computed]
    public function chartMunicipios()
    {
        $datos = $this->datosPorMunicipio;

        // Solo municipios con expedientes, top 15
        $conDatos = $datos->filter(fn($d) => $d['total'] > 0)->take(15);

        return [
            'labels' => $conDatos->pluck('nombre')->values()->toArray(),
            'datasets' => [['label' => 'Aprobados', 'data' => $conDatos->pluck('aprobados')->values()->toArray(), 'color' => 'rgba(16, 185, 129, 0.7)'], ['label' => 'En Proceso', 'data' => $conDatos->map(fn($d) => $d['recibidos'] + $d['en_revision'] + $d['completos'] + $d['incompletos'])->values()->toArray(), 'color' => 'rgba(234, 179, 8, 0.7)'], ['label' => 'Rechazados', 'data' => $conDatos->pluck('rechazados')->values()->toArray(), 'color' => 'rgba(239, 68, 68, 0.7)'], ['label' => 'Archivados', 'data' => $conDatos->pluck('archivados')->values()->toArray(), 'color' => 'rgba(148, 163, 184, 0.7)']],
        ];
    }

    #[Computed]
    public function chartFinanciero()
    {
        $datos = $this->datosPorMunicipio;

        $conMontos = $datos->filter(fn($d) => $d['monto_contratado'] > 0 || $d['monto_aprobado'] > 0)->take(15);

        return [
            'labels' => $conMontos->pluck('nombre')->values()->toArray(),
            'contratado' => $conMontos->pluck('monto_contratado')->values()->toArray(),
            'aprobado' => $conMontos->pluck('monto_aprobado')->values()->toArray(),
        ];
    }

    // ---- Acciones ----

    public function limpiarFiltros(): void
    {
        $this->periodo = 'mes_actual';
        $this->municipio_id = '';
        $this->setFechasPeriodo();
        $this->resetComputadas();
    }

    public function exportarPdf(): void
    {
        $vista = match ($this->tab) {
            'resumen' => 'reportes.pdf.resumen-general',
            'municipio' => 'reportes.pdf.por-municipio',
            'tipo' => 'reportes.pdf.por-tipo',
            'financiero' => 'reportes.pdf.financiero',
        };

        $nombreArchivo = match ($this->tab) {
            'resumen' => 'Reporte_Resumen_General',
            'municipio' => 'Reporte_Por_Municipio',
            'tipo' => 'Reporte_Por_Tipo_Solicitud',
            'financiero' => 'Reporte_Financiero',
        };

        $periodoTexto = $this->getPeriodoTexto();
        $municipioNombre = $this->municipio_id ? Municipio::find($this->municipio_id)?->nombre ?? 'Todos' : 'Todos los municipios';

        $data = [
            'periodoTexto' => $periodoTexto,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'municipioNombre' => $municipioNombre,
            'generadoPor' => Auth::user()->nombre_completo ?? Auth::user()->nombres,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ];

        // Agregar datos específicos por tab
        match ($this->tab) {
            'resumen' => ($data['estadisticas'] = $this->estadisticas),
            'municipio' => ($data['datos'] = $this->datosPorMunicipio),
            'tipo' => ($data['datos'] = $this->datosPorTipo),
            'financiero' => ($data = array_merge($data, [
                'resumen' => $this->resumenFinanciero,
                'datos' => $this->datosFinancieros,
            ])),
        };

        $pdf = Pdf::loadView($vista, $data)->setPaper('letter', 'landscape');

        $filename = "{$nombreArchivo}_{$this->fecha_desde}_a_{$this->fecha_hasta}.pdf";

        $this->dispatch('descargar-pdf', [
            'contenido' => base64_encode($pdf->output()),
            'nombre' => $filename,
        ]);
    }

    // ---- Helpers ----

    private function getPeriodoTexto(): string
    {
        if (!$this->fecha_desde || !$this->fecha_hasta) {
            return 'Sin período';
        }

        $desde = Carbon::parse($this->fecha_desde);
        $hasta = Carbon::parse($this->fecha_hasta);

        if ($desde->isSameMonth($hasta)) {
            return $desde->translatedFormat('F Y');
        }

        return $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y');
    }

    private function resetComputadas(): void
    {
        unset($this->estadisticas, $this->datosPorMunicipio, $this->datosPorTipo, $this->datosFinancieros, $this->resumenFinanciero, $this->chartEstados, $this->chartMunicipios, $this->chartFinanciero);
    }

    public function formatoMoneda(float|int|null $monto): string
    {
        return 'Q ' . number_format($monto ?? 0, 2);
    }
}; ?>

<div>
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-chart-bar class="w-6 h-6" />
                </div>
                Reportes
            </h1>
            <p class="text-base-content/60 text-sm mt-1">Informes y estadísticas del sistema de expedientes</p>
        </div>
    </div>

    {{-- Barra de Filtros --}}
    <div class="card bg-base-100 shadow-lg border border-base-300 mb-6">
        <div class="card-body p-4">
            <div class="flex flex-wrap items-end gap-3">
                {{-- Período --}}
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Período</span>
                    </label>
                    <select wire:model.live="periodo" class="select select-bordered select-sm w-full sm:w-48">
                        <option value="mes_actual">Mes Actual</option>
                        <option value="este_anio">Este Año</option>
                        <option value="personalizado">Rango Personalizado</option>
                    </select>
                </div>

                {{-- Fecha Desde --}}
                @if ($periodo === 'personalizado')
                    <div class="form-control w-full sm:w-auto">
                        <label class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wider">Desde</span>
                        </label>
                        <input type="date" wire:model.live.debounce.300ms="fecha_desde"
                            class="input input-bordered input-sm w-full sm:w-44" />
                    </div>

                    {{-- Fecha Hasta --}}
                    <div class="form-control w-full sm:w-auto">
                        <label class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wider">Hasta</span>
                        </label>
                        <input type="date" wire:model.live.debounce.300ms="fecha_hasta"
                            class="input input-bordered input-sm w-full sm:w-44" />
                    </div>
                @endif

                {{-- Municipio --}}
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold uppercase tracking-wider">Municipio</span>
                    </label>
                    <select wire:model.live="municipio_id" class="select select-bordered select-sm w-full sm:w-56">
                        <option value="">Todos los municipios</option>
                        @foreach ($this->municipiosDisponibles as $mun)
                            <option value="{{ $mun->id }}">{{ $mun->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Botones --}}
                <div class="flex gap-2 ml-auto">
                    <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm gap-1">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        Limpiar
                    </button>
                    <button wire:click="exportarPdf" class="btn btn-primary btn-sm gap-1">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                        Exportar PDF
                    </button>
                </div>
            </div>

            {{-- Período activo --}}
            <div class="flex items-center gap-2 mt-2 text-xs text-base-content/50">
                <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                <span>
                    {{ $fecha_desde ? \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') : '--' }}
                    al
                    {{ $fecha_hasta ? \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') : '--' }}
                </span>
                @if ($municipio_id)
                    <span class="badge badge-primary badge-xs">
                        {{ $this->municipiosDisponibles->firstWhere('id', $municipio_id)?->nombre }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Pestañas --}}
    <div role="tablist" class="tabs tabs-border mb-6">
        <button wire:click="$set('tab', 'resumen')" role="tab"
            class="tab gap-2 {{ $tab === 'resumen' ? 'tab-active font-semibold' : '' }}">
            <x-heroicon-o-chart-pie class="w-4 h-4" />
            Resumen General
        </button>
        <button wire:click="$set('tab', 'municipio')" role="tab"
            class="tab gap-2 {{ $tab === 'municipio' ? 'tab-active font-semibold' : '' }}">
            <x-heroicon-o-building-library class="w-4 h-4" />
            Por Municipio
        </button>
        <button wire:click="$set('tab', 'tipo')" role="tab"
            class="tab gap-2 {{ $tab === 'tipo' ? 'tab-active font-semibold' : '' }}">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
            Por Tipo de Solicitud
        </button>
        <button wire:click="$set('tab', 'financiero')" role="tab"
            class="tab gap-2 {{ $tab === 'financiero' ? 'tab-active font-semibold' : '' }}">
            <x-heroicon-o-banknotes class="w-4 h-4" />
            Financiero
        </button>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading.flex class="justify-center items-center py-12">
        <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: RESUMEN GENERAL --}}
    {{-- ============================================================ --}}
    @if ($tab === 'resumen')
        <div wire:loading.remove>
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                {{-- Total --}}
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-figure text-primary">
                        <x-heroicon-o-folder-open class="w-6 h-6" />
                    </div>
                    <div class="stat-title text-xs">Total</div>
                    <div class="stat-value text-2xl text-primary">{{ $this->estadisticas['total'] }}</div>
                    <div class="stat-desc text-xs">Expedientes</div>
                </div>

                {{-- En Proceso --}}
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-figure text-warning">
                        <x-heroicon-o-clock class="w-6 h-6" />
                    </div>
                    <div class="stat-title text-xs">En Proceso</div>
                    <div class="stat-value text-2xl text-warning">{{ $this->estadisticas['enProceso'] }}</div>
                    <div class="stat-desc text-xs">Activos</div>
                </div>

                {{-- Aprobados --}}
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-figure text-success">
                        <x-heroicon-o-check-circle class="w-6 h-6" />
                    </div>
                    <div class="stat-title text-xs">Aprobados</div>
                    <div class="stat-value text-2xl text-success">{{ $this->estadisticas['aprobados'] }}</div>
                    <div class="stat-desc text-xs">Finalizados</div>
                </div>

                {{-- Rechazados --}}
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-figure text-error">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </div>
                    <div class="stat-title text-xs">Rechazados</div>
                    <div class="stat-value text-2xl text-error">{{ $this->estadisticas['rechazados'] }}</div>
                    <div class="stat-desc text-xs">Denegados</div>
                </div>

                {{-- Monto Contratado --}}
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-figure text-info">
                        <x-heroicon-o-currency-dollar class="w-6 h-6" />
                    </div>
                    <div class="stat-title text-xs">Contratado</div>
                    <div class="stat-value text-lg">{{ $this->formatoMoneda($this->estadisticas['montoContratado']) }}
                    </div>
                    <div class="stat-desc text-xs">Total montos</div>
                </div>

                {{-- Monto Aprobado --}}
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-figure text-accent">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                    <div class="stat-title text-xs">Aprobado</div>
                    <div class="stat-value text-lg">{{ $this->formatoMoneda($this->estadisticas['montoAprobado']) }}
                    </div>
                    <div class="stat-desc text-xs">Total revisiones</div>
                </div>
            </div>

            {{-- Desglose por Estado --}}
            <div class="card bg-base-100 shadow-lg border border-base-300 mb-6">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm text-base-content/70 mb-3">Desglose por Estado</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-info gap-1 p-3">
                            <span class="font-bold">{{ $this->estadisticas['recibidos'] }}</span> Recibidos
                        </span>
                        <span class="badge badge-warning gap-1 p-3">
                            <span class="font-bold">{{ $this->estadisticas['enRevision'] }}</span> En Revisión
                        </span>
                        <span class="badge badge-success gap-1 p-3">
                            <span class="font-bold">{{ $this->estadisticas['completos'] }}</span> Completos
                        </span>
                        <span class="badge badge-error gap-1 p-3">
                            <span class="font-bold">{{ $this->estadisticas['incompletos'] }}</span> Incompletos
                        </span>
                        <span class="badge badge-accent gap-1 p-3">
                            <span class="font-bold">{{ $this->estadisticas['aprobados'] }}</span> Aprobados
                        </span>
                        <span class="badge gap-1 p-3 badge-outline border-error text-error">
                            <span class="font-bold">{{ $this->estadisticas['rechazados'] }}</span> Rechazados
                        </span>
                        <span class="badge badge-ghost gap-1 p-3">
                            <span class="font-bold">{{ $this->estadisticas['archivados'] }}</span> Archivados
                        </span>
                    </div>
                </div>
            </div>

            {{-- Gráfica de Estados (Pie) --}}
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm text-base-content/70 mb-3">Distribución por Estado</h3>
                    @if ($this->estadisticas['total'] > 0)
                        <div class="w-full h-72 max-w-md mx-auto" wire:ignore x-data="reportePieChart(@js($this->chartEstados))"
                            x-init="initChart()" x-effect="updateChart(@js($this->chartEstados))">
                            <canvas x-ref="pieChart" class="w-full h-full"></canvas>
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/40">
                            <x-heroicon-o-chart-pie class="w-12 h-12 mx-auto mb-2 opacity-30" />
                            <p>No hay datos para el período seleccionado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB: POR MUNICIPIO --}}
    {{-- ============================================================ --}}
    @if ($tab === 'municipio')
        <div wire:loading.remove>
            {{-- Tabla comparativa --}}
            <div class="card bg-base-100 shadow-lg border border-base-300 mb-6">
                <div class="card-body p-4">
                    <h3 class="font-semibold flex items-center gap-2 mb-4">
                        <x-heroicon-o-building-library class="w-5 h-5 text-primary" />
                        Comparativo por Municipio
                        <span class="badge badge-neutral badge-sm">{{ $this->datosPorMunicipio->count() }}</span>
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th class="font-semibold">Municipio</th>
                                    <th class="text-center font-semibold">Recibidos</th>
                                    <th class="text-center font-semibold">En Revisión</th>
                                    <th class="text-center font-semibold">Completos</th>
                                    <th class="text-center font-semibold">Incompletos</th>
                                    <th class="text-center font-semibold">Aprobados</th>
                                    <th class="text-center font-semibold">Rechazados</th>
                                    <th class="text-center font-semibold">Archivados</th>
                                    <th class="text-center font-semibold">Total</th>
                                    <th class="text-right font-semibold">Monto Contratado</th>
                                    <th class="text-right font-semibold">Monto Aprobado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->datosPorMunicipio as $fila)
                                    @if ($fila['total'] > 0)
                                        <tr>
                                            <td class="font-medium">{{ $fila['nombre'] }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-info badge-sm">{{ $fila['recibidos'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-warning badge-sm">{{ $fila['en_revision'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-success badge-sm">{{ $fila['completos'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-error badge-sm">{{ $fila['incompletos'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-accent badge-sm">{{ $fila['aprobados'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-outline badge-sm border-error text-error">{{ $fila['rechazados'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-ghost badge-sm">{{ $fila['archivados'] }}</span>
                                            </td>
                                            <td class="text-center font-bold">{{ $fila['total'] }}</td>
                                            <td class="text-right text-sm">
                                                {{ $this->formatoMoneda($fila['monto_contratado']) }}</td>
                                            <td class="text-right text-sm">
                                                {{ $this->formatoMoneda($fila['monto_aprobado']) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-8 text-base-content/40">
                                            No hay datos para el período seleccionado
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($this->datosPorMunicipio->sum('total') > 0)
                                <tfoot>
                                    <tr class="font-bold bg-base-200/50">
                                        <td>TOTAL</td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('recibidos') }}</td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('en_revision') }}
                                        </td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('completos') }}</td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('incompletos') }}
                                        </td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('aprobados') }}</td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('rechazados') }}</td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('archivados') }}</td>
                                        <td class="text-center">{{ $this->datosPorMunicipio->sum('total') }}</td>
                                        <td class="text-right">
                                            {{ $this->formatoMoneda($this->datosPorMunicipio->sum('monto_contratado')) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $this->formatoMoneda($this->datosPorMunicipio->sum('monto_aprobado')) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Gráfica barras apiladas por municipio --}}
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm text-base-content/70 mb-3">Composición de Estados por Municipio
                    </h3>
                    @if ($this->datosPorMunicipio->sum('total') > 0)
                        <div class="w-full h-80" wire:ignore x-data="reporteStackedBarChart(@js($this->chartMunicipios))" x-init="initChart()"
                            x-effect="updateChart(@js($this->chartMunicipios))">
                            <canvas x-ref="stackedChart" class="w-full h-full"></canvas>
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/40">
                            <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto mb-2 opacity-30" />
                            <p>No hay datos para graficar</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB: POR TIPO DE SOLICITUD --}}
    {{-- ============================================================ --}}
    @if ($tab === 'tipo')
        <div wire:loading.remove>
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-semibold flex items-center gap-2 mb-4">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-primary" />
                        Desglose por Tipo de Solicitud
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th class="font-semibold">Tipo de Solicitud</th>
                                    <th class="text-center font-semibold">Total</th>
                                    <th class="text-center font-semibold">Aprobados</th>
                                    <th class="text-center font-semibold">Pendientes</th>
                                    <th class="text-center font-semibold">Rechazados</th>
                                    <th class="text-right font-semibold">Monto Contratado</th>
                                    <th class="text-right font-semibold">Monto Aprobado</th>
                                    <th class="text-center font-semibold">% Aprobación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->datosPorTipo as $fila)
                                    <tr>
                                        <td class="font-medium">{{ $fila['nombre'] }}</td>
                                        <td class="text-center font-bold">{{ $fila['total'] }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-success badge-sm">{{ $fila['aprobados'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge badge-warning badge-sm">{{ $fila['pendientes'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-error badge-sm">{{ $fila['rechazados'] }}</span>
                                        </td>
                                        <td class="text-right text-sm">
                                            {{ $this->formatoMoneda($fila['monto_contratado']) }}</td>
                                        <td class="text-right text-sm">
                                            {{ $this->formatoMoneda($fila['monto_aprobado']) }}</td>
                                        <td class="text-center">
                                            @if ($fila['total'] > 0)
                                                <div class="flex items-center gap-2 justify-center">
                                                    <progress
                                                        class="progress w-16 {{ $fila['porcentaje_aprobacion'] >= 70 ? 'progress-success' : ($fila['porcentaje_aprobacion'] >= 40 ? 'progress-warning' : 'progress-error') }}"
                                                        value="{{ $fila['porcentaje_aprobacion'] }}"
                                                        max="100"></progress>
                                                    <span
                                                        class="text-xs font-semibold">{{ $fila['porcentaje_aprobacion'] }}%</span>
                                                </div>
                                            @else
                                                <span class="text-base-content/30">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-base-content/40">
                                            No hay tipos de solicitud registrados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($this->datosPorTipo->sum('total') > 0)
                                <tfoot>
                                    <tr class="font-bold bg-base-200/50">
                                        <td>TOTAL</td>
                                        <td class="text-center">{{ $this->datosPorTipo->sum('total') }}</td>
                                        <td class="text-center">{{ $this->datosPorTipo->sum('aprobados') }}</td>
                                        <td class="text-center">{{ $this->datosPorTipo->sum('pendientes') }}</td>
                                        <td class="text-center">{{ $this->datosPorTipo->sum('rechazados') }}</td>
                                        <td class="text-right">
                                            {{ $this->formatoMoneda($this->datosPorTipo->sum('monto_contratado')) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $this->formatoMoneda($this->datosPorTipo->sum('monto_aprobado')) }}</td>
                                        <td class="text-center">
                                            @php
                                                $totalTipo = $this->datosPorTipo->sum('total');
                                                $aprobadosTipo = $this->datosPorTipo->sum('aprobados');
                                                $pctGlobal =
                                                    $totalTipo > 0 ? round(($aprobadosTipo / $totalTipo) * 100, 1) : 0;
                                            @endphp
                                            <span class="text-xs font-semibold">{{ $pctGlobal }}%</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB: FINANCIERO --}}
    {{-- ============================================================ --}}
    @if ($tab === 'financiero')
        <div wire:loading.remove>
            {{-- Stats Financieros --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-title text-xs">Monto Contratado</div>
                    <div class="stat-value text-lg text-primary">
                        {{ $this->formatoMoneda($this->resumenFinanciero['montoContratado']) }}</div>
                </div>
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-title text-xs">Monto Aprobado</div>
                    <div class="stat-value text-lg text-success">
                        {{ $this->formatoMoneda($this->resumenFinanciero['montoAprobado']) }}</div>
                </div>
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-title text-xs">Diferencia</div>
                    <div
                        class="stat-value text-lg {{ $this->resumenFinanciero['diferencia'] >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $this->formatoMoneda($this->resumenFinanciero['diferencia']) }}
                    </div>
                    <div class="stat-desc text-xs">
                        {{ $this->resumenFinanciero['variacion'] >= 0 ? '+' : '' }}{{ $this->resumenFinanciero['variacion'] }}%
                    </div>
                </div>
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3">
                    <div class="stat-title text-xs">Promedio/Exp.</div>
                    <div class="stat-value text-lg">
                        {{ $this->formatoMoneda($this->resumenFinanciero['promedioMonto']) }}</div>
                    <div class="stat-desc text-xs">Monto promedio</div>
                </div>
                <div class="stat bg-base-100 shadow border border-base-300 rounded-box p-3 col-span-2">
                    <div class="stat-title text-xs">Promedio Días Trámite</div>
                    <div class="stat-value text-lg text-info">{{ $this->resumenFinanciero['promedioDias'] }} días
                    </div>
                    <div class="stat-desc text-xs">De recibido a aprobado</div>
                </div>
            </div>

            {{-- Tabla detalle financiero --}}
            <div class="card bg-base-100 shadow-lg border border-base-300 mb-6">
                <div class="card-body p-4">
                    <h3 class="font-semibold flex items-center gap-2 mb-4">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-primary" />
                        Detalle Financiero
                        <span class="badge badge-neutral badge-sm">{{ $this->datosFinancieros->count() }}</span>
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th class="font-semibold">SNIP</th>
                                    <th class="font-semibold">Municipio</th>
                                    <th class="font-semibold">Tipo</th>
                                    <th class="text-right font-semibold">Monto Contratado</th>
                                    <th class="text-right font-semibold">Monto Aprobado</th>
                                    <th class="text-right font-semibold">Diferencia</th>
                                    <th class="text-center font-semibold">Días Trámite</th>
                                    <th class="text-center font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->datosFinancieros as $fila)
                                    <tr>
                                        <td class="font-mono text-sm">{{ $fila['codigo_snip'] }}</td>
                                        <td>{{ $fila['municipio'] }}</td>
                                        <td class="text-sm">{{ $fila['tipo_solicitud'] }}</td>
                                        <td class="text-right text-sm">
                                            {{ $this->formatoMoneda($fila['monto_contratado']) }}</td>
                                        <td class="text-right text-sm">
                                            {{ $this->formatoMoneda($fila['monto_aprobado']) }}</td>
                                        <td
                                            class="text-right text-sm {{ $fila['diferencia'] >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ $this->formatoMoneda($fila['diferencia']) }}
                                        </td>
                                        <td class="text-center">{{ $fila['dias_tramite'] }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $fila['estado_badge'] }} badge-sm">{{ $fila['estado'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-base-content/40">
                                            No hay expedientes con revisión financiera en este período
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Gráfica Monto Contratado vs Aprobado --}}
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm text-base-content/70 mb-3">Monto Contratado vs Aprobado por
                        Municipio</h3>
                    @if (count($this->chartFinanciero['labels']) > 0)
                        <div class="w-full h-80" wire:ignore x-data="reporteFinancieroChart(@js($this->chartFinanciero))" x-init="initChart()"
                            x-effect="updateChart(@js($this->chartFinanciero))">
                            <canvas x-ref="financieroChart" class="w-full h-full"></canvas>
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/40">
                            <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto mb-2 opacity-30" />
                            <p>No hay datos financieros para graficar</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Scripts --}}
@script
    <script>
        // Descarga de PDF via base64
        Livewire.on('descargar-pdf', ([data]) => {
            const link = document.createElement('a');
            link.href = 'data:application/pdf;base64,' + data.contenido;
            link.download = data.nombre;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Gráfica Pie/Dona - Distribución por Estado
        Alpine.data('reportePieChart', (initialData) => {
            let chart = null;

            return {
                initChart() {
                    this.destroyChart();
                    const canvas = this.$refs.pieChart;
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    chart = new Chart(ctx, {
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
                    if (!chart) return;

                    chart.data.labels = newData.labels;
                    chart.data.datasets[0].data = newData.data;
                    chart.data.datasets[0].backgroundColor = newData.colors;
                    chart.update();
                },
                destroyChart() {
                    if (chart) {
                        chart.destroy();
                        chart = null;
                    }
                }
            };
        });

        // Gráfica Barras Apiladas - Por Municipio
        Alpine.data('reporteStackedBarChart', (initialData) => {
            let chart = null;

            return {
                initChart() {
                    this.destroyChart();
                    const canvas = this.$refs.stackedChart;
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    const datasets = initialData.datasets.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        backgroundColor: ds.color,
                        borderWidth: 1,
                        borderColor: 'rgba(255,255,255,0.5)',
                        borderRadius: 2,
                    }));
                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: initialData.labels,
                            datasets
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 11
                                        },
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
                                            ` ${ctx.dataset.label}: ${ctx.parsed.x} expediente${ctx.parsed.x !== 1 ? 's' : ''}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                y: {
                                    stacked: true,
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
                    if (!chart) return;

                    chart.data.labels = newData.labels;

                    if ((chart.data.datasets?.length ?? 0) !== (newData.datasets?.length ?? 0)) {
                        chart.data.datasets = newData.datasets.map(ds => ({
                            label: ds.label,
                            data: ds.data,
                            backgroundColor: ds.color,
                            borderWidth: 1,
                            borderColor: 'rgba(255,255,255,0.5)',
                            borderRadius: 2,
                        }));
                    } else {
                        newData.datasets.forEach((ds, i) => {
                            if (chart.data.datasets[i]) {
                                chart.data.datasets[i].label = ds.label;
                                chart.data.datasets[i].data = ds.data;
                                chart.data.datasets[i].backgroundColor = ds.color;
                            }
                        });
                    }

                    chart.update();
                },
                destroyChart() {
                    if (chart) {
                        chart.destroy();
                        chart = null;
                    }
                }
            };
        });

        // Gráfica Barras Comparativas - Financiero
        Alpine.data('reporteFinancieroChart', (initialData) => {
            let chart = null;

            return {
                initChart() {
                    this.destroyChart();
                    const canvas = this.$refs.financieroChart;
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: initialData.labels,
                            datasets: [{
                                    label: 'Monto Contratado',
                                    data: initialData.contratado,
                                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                    borderSkipped: false,
                                },
                                {
                                    label: 'Monto Aprobado',
                                    data: initialData.aprobado,
                                    backgroundColor: 'rgba(16, 185, 129, 0.6)',
                                    borderColor: 'rgb(16, 185, 129)',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                    borderSkipped: false,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 11
                                        },
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
                                            ` ${ctx.dataset.label}: Q ${ctx.parsed.y.toLocaleString('es-GT', { minimumFractionDigits: 2 })}`
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            size: 11
                                        },
                                        callback: (v) => 'Q ' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' :
                                            v)
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        maxRotation: 45
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
                    if (!chart) return;

                    chart.data.labels = newData.labels;
                    chart.data.datasets[0].data = newData.contratado;
                    chart.data.datasets[1].data = newData.aprobado;
                    chart.update();
                },
                destroyChart() {
                    if (chart) {
                        chart.destroy();
                        chart = null;
                    }
                }
            };
        });
    </script>
@endscript
