<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\RevisionFinanciera;
use App\Models\TipoSolicitud;
use App\Models\Bitacora;

new class extends Component {
    public int $expedienteId;

    // Campos del formulario
    public string $tipo_solicitud_id = '';
    public string $estado = '';
    public string $accion = '';
    public string $monto_aprobado = '';
    public string $observaciones = '';

    public function mount(int $expedienteId)
    {
        $this->expedienteId = $expedienteId;

        // Preseleccionar la siguiente fase disponible
        $siguiente = $this->siguienteFase;
        if ($siguiente) {
            $this->tipo_solicitud_id = (string) $siguiente->id;
        }
    }

    #[Computed]
    public function expediente()
    {
        return Expediente::with(['revisionesFinancieras.tipoSolicitud'])->findOrFail($this->expedienteId);
    }

    #[Computed]
    public function tiposSolicitud()
    {
        return TipoSolicitud::orderBy('id')->get();
    }

    /**
     * Fases (tipos de solicitud) que ya tienen revisión Completa
     */
    #[Computed]
    public function fasesCompletadas()
    {
        return $this->expediente->revisionesFinancieras()->where('estado', RevisionFinanciera::ESTADO_COMPLETO)->distinct('tipo_solicitud_id')->pluck('tipo_solicitud_id')->toArray();
    }

    /**
     * Fases que están actualmente rechazadas (última revisión es Rechazar, no reactivada después)
     */
    #[Computed]
    public function fasesRechazadas()
    {
        $rechazadas = [];
        $revisiones = $this->expediente->revisionesFinancieras;

        foreach ($this->tiposSolicitud as $ts) {
            $revisionesFase = $revisiones->where('tipo_solicitud_id', $ts->id)->sortByDesc('id');
            $ultima = $revisionesFase->first();

            if ($ultima && $ultima->accion === RevisionFinanciera::ACCION_RECHAZAR) {
                $rechazadas[] = $ts->id;
            }
        }

        return $rechazadas;
    }

    /**
     * Fases bloqueadas (completadas + rechazadas + posteriores a una rechazada)
     * Si una fase es rechazada, todas las posteriores también se bloquean
     */
    #[Computed]
    public function fasesBloqueadas()
    {
        $bloqueadas = $this->fasesCompletadas;
        $rechazadas = $this->fasesRechazadas;

        // Si hay alguna fase rechazada, bloquear esa y todas las posteriores
        foreach ($this->tiposSolicitud as $ts) {
            if (in_array($ts->id, $rechazadas)) {
                // Desde esta fase en adelante, todo bloqueado
                $encontrada = false;
                foreach ($this->tiposSolicitud as $ts2) {
                    if ($ts2->id === $ts->id) {
                        $encontrada = true;
                    }
                    if ($encontrada && !in_array($ts2->id, $bloqueadas)) {
                        $bloqueadas[] = $ts2->id;
                    }
                }
                break; // Solo necesitamos la primera rechazada
            }
        }

        return array_unique($bloqueadas);
    }

    /**
     * La siguiente fase que toca (primera no completada ni rechazada)
     */
    #[Computed]
    public function siguienteFase()
    {
        $bloqueadas = $this->fasesBloqueadas;
        return $this->tiposSolicitud->first(fn($ts) => !in_array($ts->id, $bloqueadas));
    }

    /**
     * Número de revisión para la fase seleccionada
     */
    #[Computed]
    public function numeroRevision()
    {
        if (!$this->tipo_solicitud_id) {
            return 1;
        }

        return $this->expediente->revisionesFinancieras()->where('tipo_solicitud_id', $this->tipo_solicitud_id)->count() + 1;
    }

    /**
     * Monto total aprobado en todas las fases
     */
    #[Computed]
    public function montoTotalAprobado()
    {
        return (float) $this->expediente->revisionesFinancieras()->where('estado', RevisionFinanciera::ESTADO_COMPLETO)->whereNotNull('monto_aprobado')->sum('monto_aprobado');
    }

    /**
     * Monto restante disponible para aprobar
     */
    #[Computed]
    public function montoRestante()
    {
        $contrato = (float) $this->expediente->monto_contrato;
        if (!$contrato) {
            return null;
        }
        return max(0, $contrato - $this->montoTotalAprobado);
    }

    /**
     * Verificar si todas las fases están completas
     */
    #[Computed]
    public function todasFasesCompletas()
    {
        return count($this->fasesCompletadas) >= $this->tiposSolicitud->count();
    }

    /**
     * Verificar si hay fases rechazadas (no se pueden agregar más revisiones)
     */
    #[Computed]
    public function hayFasesRechazadas()
    {
        return count($this->fasesRechazadas) > 0;
    }

    /**
     * Verificar si ya no se puede seguir (todas bloqueadas)
     */
    #[Computed]
    public function sinFasesDisponibles()
    {
        return $this->siguienteFase === null;
    }

    /**
     * Resetear acción y monto cuando cambia el estado
     */
    public function updatedEstado()
    {
        if ($this->estado === RevisionFinanciera::ESTADO_INCOMPLETO) {
            // Preseleccionar solicitar correcciones
            $this->accion = RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES;
            $this->monto_aprobado = '';
        } elseif ($this->estado === RevisionFinanciera::ESTADO_COMPLETO) {
            // Aprobación automática de la fase
            $this->accion = RevisionFinanciera::ACCION_APROBAR;
        } else {
            $this->accion = '';
            $this->monto_aprobado = '';
        }
    }

    #[On('info-expediente-completada')]
    #[On('fase-reactivada')]
    public function refrescarExpediente()
    {
        unset($this->expediente);
        unset($this->montoRestante);
        unset($this->montoTotalAprobado);
        unset($this->fasesCompletadas);
        unset($this->fasesRechazadas);
        unset($this->fasesBloqueadas);
        unset($this->siguienteFase);
    }

    public function guardar()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);

        $user = auth()->user();
        if (!$user->can('revisarFinanciera', $expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para registrar revisiones financieras.');
            return;
        }

        // No permitir nuevas revisiones si no hay fases disponibles
        if ($this->sinFasesDisponibles) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No hay fases disponibles para registrar revisiones.');
            return;
        }

        // Verificar que la fase seleccionada no esté bloqueada (completada o rechazada)
        $fasesBloqueadas = $this->fasesBloqueadas;
        if (in_array((int) $this->tipo_solicitud_id, $fasesBloqueadas)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Esta fase ya está completada o rechazada y no acepta más revisiones.');
            return;
        }

        // Forzar acción según estado
        if ($this->estado === RevisionFinanciera::ESTADO_COMPLETO) {
            $this->accion = RevisionFinanciera::ACCION_APROBAR;
        }

        $rules = [
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'estado' => 'required|in:' . implode(',', RevisionFinanciera::getEstados()),
            'observaciones' => 'required|string|max:2000',
        ];

        // Acción obligatoria para ambos estados
        $rules['accion'] = 'required|in:' . implode(',', RevisionFinanciera::getAcciones());

        // Monto aprobado requerido si el estado es Completo
        if ($this->estado === RevisionFinanciera::ESTADO_COMPLETO) {
            $maxMonto = $this->montoRestante ?? 999999999;
            $rules['monto_aprobado'] = "required|numeric|min:0.01|max:{$maxMonto}";
        } elseif ($this->monto_aprobado !== '') {
            $rules['monto_aprobado'] = 'numeric|min:0';
        }

        $validated = $this->validate($rules, [
            'tipo_solicitud_id.required' => 'La fase de desembolso es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'observaciones.required' => 'Las observaciones son obligatorias.',
            'observaciones.max' => 'Las observaciones no pueden exceder 2000 caracteres.',
            'accion.required' => 'La acción es obligatoria.',
            'accion.in' => 'La acción seleccionada no es válida.',
            'monto_aprobado.required' => 'El monto aprobado es obligatorio para fases completas.',
            'monto_aprobado.numeric' => 'El monto debe ser un número válido.',
            'monto_aprobado.min' => 'El monto debe ser mayor a 0.',
            'monto_aprobado.max' => 'El monto excede el restante disponible (Q ' . number_format($this->montoRestante ?? 0, 2) . ').',
        ]);

        // Verificar que no se registre revisión de una fase que no corresponde (secuencialidad)
        $fasesCompletadas = $this->fasesCompletadas;
        $tiposSolicitud = $this->tiposSolicitud;
        $faseSeleccionada = (int) $this->tipo_solicitud_id;

        // Validar que la fase seleccionada sea la siguiente en secuencia (si va a completar)
        if ($this->estado === RevisionFinanciera::ESTADO_COMPLETO) {
            $siguienteFase = $this->siguienteFase;
            if ($siguienteFase && $faseSeleccionada !== $siguienteFase->id) {
                $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: "Debe completar primero la fase: {$siguienteFase->nombre}");
                return;
            }
        }

        // Crear la revisión
        $revision = RevisionFinanciera::create([
            'expediente_id' => $expediente->id,
            'tipo_solicitud_id' => $this->tipo_solicitud_id,
            'numero_revision' => $this->numeroRevision,
            'revisor_id' => $user->id,
            'estado' => $this->estado,
            'accion' => $this->accion ?: null,
            'monto_aprobado' => $this->monto_aprobado !== '' ? $this->monto_aprobado : null,
            'observaciones' => $this->observaciones,
            'fecha_revision' => now(),
        ]);

        // Si se completó la ÚLTIMA fase (Pago Final 100%), aprobar el expediente
        $totalFases = $tiposSolicitud->count();
        $fasesCompletadasDespues = $expediente->fresh()->revisionesFinancieras()->where('estado', RevisionFinanciera::ESTADO_COMPLETO)->distinct('tipo_solicitud_id')->count('tipo_solicitud_id');

        if ($fasesCompletadasDespues >= $totalFases) {
            $expediente->aprobar();
        }

        // Registrar en bitácora
        $fase = TipoSolicitud::find($this->tipo_solicitud_id);
        $faseNombre = $fase ? $fase->nombre : 'N/A';
        $montoTexto = $this->monto_aprobado ? ' – Monto: Q' . number_format((float) $this->monto_aprobado, 2) : '';
        Bitacora::registrarRevision("Revisión #{$this->numeroRevision} de {$faseNombre} en Expediente {$expediente->codigo_snip} – Estado: {$this->estado}{$montoTexto}", $expediente->id);

        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: '¡Revisión registrada con éxito!');
        $this->redirectRoute('expedientes.show', $expediente->id, navigate: true);
    }

    public function cancelar()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);
        $this->redirectRoute('expedientes.show', $expediente->id, navigate: true);
    }
};
?>

<div>
    {{-- Progreso de fases --}}
    <div class="mb-6">
        <h4 class="text-sm font-semibold mb-3 flex items-center gap-2">
            <x-heroicon-o-flag class="w-4 h-4 text-accent" />
            Progreso de Desembolsos
        </h4>
        <ul class="steps steps-horizontal w-full text-xs">
            @foreach ($this->tiposSolicitud as $ts)
                @php
                    $completada = in_array($ts->id, $this->fasesCompletadas);
                    $rechazada = in_array($ts->id, $this->fasesRechazadas);
                    $esActual = $this->siguienteFase && $ts->id === $this->siguienteFase->id;
                    $stepClass = $completada
                        ? 'step-success'
                        : ($rechazada
                            ? 'step-error'
                            : ($esActual
                                ? 'step-warning'
                                : ''));
                @endphp
                <li class="step {{ $stepClass }}"
                    data-content="{{ $completada ? '✓' : ($rechazada ? '✗' : ($esActual ? '●' : '')) }}">
                    <span class="hidden sm:inline">{{ $ts->nombre }}</span>
                    <span class="sm:hidden">{{ Str::limit($ts->nombre, 8) }}</span>
                </li>
            @endforeach
        </ul>

        {{-- Resumen de montos --}}
        @if ($this->expediente->monto_contrato)
            <div class="mt-4 space-y-3 rounded-box border border-base-content/10 bg-base-100 p-4">
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-box border border-base-content/10 bg-base-200/70 p-3 text-center">
                        <p class="text-xs text-base-content/50">Monto del Contrato</p>
                        <p class="font-bold text-sm">{{ $this->expediente->monto_formateado }}</p>
                    </div>
                    <div class="rounded-box border border-success/20 bg-success/10 p-3 text-center">
                        <p class="text-xs text-base-content/50">Total Aprobado</p>
                        <p class="font-bold text-sm text-success">Q {{ number_format($this->montoTotalAprobado, 2) }}
                        </p>
                    </div>
                    <div class="rounded-box border border-warning/20 bg-warning/10 p-3 text-center">
                        <p class="text-xs text-base-content/50">Restante</p>
                        <p class="font-bold text-sm text-warning">Q {{ number_format($this->montoRestante ?? 0, 2) }}
                        </p>
                    </div>
                </div>
                @php
                    $porcentajeAvance =
                        $this->expediente->monto_contrato > 0
                            ? min(100, ($this->montoTotalAprobado / $this->expediente->monto_contrato) * 100)
                            : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs text-base-content/50 mb-1">
                        <span>Avance financiero</span>
                        <span>{{ number_format($porcentajeAvance, 1) }}%</span>
                    </div>
                    <progress
                        class="progress {{ $porcentajeAvance >= 100 ? 'progress-success' : 'progress-accent' }} w-full"
                        value="{{ $porcentajeAvance }}" max="100"></progress>
                </div>
            </div>
        @endif
    </div>

    {{-- Alerta si no hay fases disponibles --}}
    @if ($this->todasFasesCompletas)
        <div role="alert" class="alert alert-success mb-6">
            <x-heroicon-o-check-circle class="stroke-current shrink-0 h-6 w-6" />
            <div>
                <h3 class="font-bold">Todas las fases completadas</h3>
                <p class="text-sm">Se han completado las 4 fases de desembolso. El expediente será aprobado
                    automáticamente.</p>
            </div>
        </div>
    @elseif ($this->sinFasesDisponibles && $this->hayFasesRechazadas)
        <div role="alert" class="alert alert-error mb-6">
            <x-heroicon-o-x-circle class="stroke-current shrink-0 h-6 w-6" />
            <div>
                <h3 class="font-bold">Revisiones bloqueadas</h3>
                <p class="text-sm">Existen fases rechazadas que impiden continuar. Un administrador puede reactivar las
                    fases rechazadas desde el historial de revisiones.</p>
            </div>
        </div>
    @else
        <form wire:submit="guardar" class="space-y-6">
            {{-- Fase de desembolso y Estado --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend">Fase de Desembolso <span class="text-error">*</span></legend>
                    <select wire:model.live="tipo_solicitud_id" id="tipo_solicitud_id"
                        class="select w-full border-base-content/20 @error('tipo_solicitud_id') select-error @enderror">
                        <option value="" selected disabled>Seleccionar fase...</option>
                        @foreach ($this->tiposSolicitud as $ts)
                            @php
                                $completada = in_array($ts->id, $this->fasesCompletadas);
                                $rechazada = in_array($ts->id, $this->fasesRechazadas);
                                $bloqueada = $completada || $rechazada;
                                $revisionesEnFase = $this->expediente->revisionesFinancieras
                                    ->where('tipo_solicitud_id', $ts->id)
                                    ->count();
                            @endphp
                            <option value="{{ $ts->id }}" {{ $bloqueada ? 'disabled' : '' }}>
                                {{ $ts->nombre }}
                                {{ $completada ? '✓ Completada' : ($rechazada ? '✗ Rechazada' : ($revisionesEnFase > 0 ? "({$revisionesEnFase} revisiones)" : '')) }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_solicitud_id')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                    <p class="label text-base-content/50">
                        Revisión #{{ $this->numeroRevision }} para esta fase
                    </p>
                </fieldset>

                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend">Estado de la documentación <span class="text-error">*</span>
                    </legend>
                    <select wire:model.live="estado" id="estado"
                        class="select w-full border-base-content/20 @error('estado') select-error @enderror">
                        <option value="" selected disabled>Seleccionar estado...</option>
                        @foreach (RevisionFinanciera::getEstados() as $est)
                            <option value="{{ $est }}">{{ $est }}</option>
                        @endforeach
                    </select>
                    @error('estado')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                    <p class="label text-base-content/50">
                        ¿La documentación de esta fase está completa?
                    </p>
                </fieldset>
            </div>

            {{-- Acción y Monto --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                {{-- Completo: la acción es automática (Aprobar), se muestra solo informativo --}}
                @if ($estado === RevisionFinanciera::ESTADO_COMPLETO)
                    <fieldset class="fieldset w-full min-w-0">
                        <legend class="fieldset-legend">Acción</legend>
                        <div
                            class="flex items-center gap-2 bg-success/10 border border-success/30 rounded-btn px-4 py-3">
                            <x-heroicon-s-check-badge class="w-5 h-5 text-success" />
                            <span class="font-medium text-success">Aprobar fase</span>
                        </div>
                        <p class="mt-1 text-xs text-base-content/50 leading-relaxed whitespace-normal wrap-break-word">
                            La aprobación del expediente es automática al completar todas las fases
                        </p>
                    </fieldset>
                    {{-- Incompleto: el usuario elige la acción --}}
                @elseif ($estado === RevisionFinanciera::ESTADO_INCOMPLETO)
                    <fieldset class="fieldset w-full min-w-0">
                        <legend class="fieldset-legend">Acción <span class="text-error">*</span></legend>
                        <select wire:model.live="accion" id="accion"
                            class="select w-full border-base-content/20 @error('accion') select-error @enderror">
                            <option value="{{ RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES }}">⚠️ Solicitar
                                correcciones</option>
                            <option value="{{ RevisionFinanciera::ACCION_RECHAZAR }}">❌ Rechazar revisión</option>
                        </select>
                        @error('accion')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-base-content/50 leading-relaxed whitespace-normal wrap-break-word">
                            Seleccione la acción a tomar sobre la documentación
                        </p>
                    </fieldset>
                @endif

                {{-- Monto aprobado (visible cuando el estado es Completo) --}}
                @if ($estado === RevisionFinanciera::ESTADO_COMPLETO)
                    <fieldset class="fieldset w-full min-w-0">
                        <legend class="fieldset-legend">Monto Aprobado <span class="text-error">*</span></legend>
                        <label
                            class="input border-base-content/20 flex items-center gap-2 @error('monto_aprobado') input-error @enderror">
                            <span class="text-base-content/60 font-bold">Q</span>
                            <input type="number" wire:model="monto_aprobado" id="monto_aprobado" step="0.01"
                                min="0"
                                @if ($this->montoRestante !== null) max="{{ $this->montoRestante }}" @endif
                                class="grow" placeholder="0.00" />
                        </label>
                        @error('monto_aprobado')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                        @if ($this->montoRestante !== null)
                            <p
                                class="mt-1 text-xs text-base-content/50 leading-relaxed whitespace-normal wrap-break-word">
                                Máximo disponible: Q {{ number_format($this->montoRestante, 2) }}
                            </p>
                        @endif
                    </fieldset>
                @endif
            </div>

            {{-- Alertas contextuales --}}
            @if ($accion === RevisionFinanciera::ACCION_RECHAZAR)
                <div role="alert" class="alert alert-error">
                    <x-heroicon-o-x-circle class="stroke-current shrink-0 h-6 w-6" />
                    <div>
                        <h3 class="font-bold">Rechazar revisión de esta fase</h3>
                        <p class="text-sm">Esta fase quedará bloqueada y no se podrán agregar más revisiones. El estado
                            del expediente <strong>no cambiará</strong>; el administrador podrá rechazarlo desde la
                            gestión de estados.</p>
                    </div>
                </div>
            @elseif ($accion === RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES)
                <div role="alert" class="alert alert-warning">
                    <x-heroicon-o-exclamation-triangle class="stroke-current shrink-0 h-6 w-6" />
                    <div>
                        <h3 class="font-bold">Solicitar correcciones</h3>
                        <p class="text-sm">Se solicitarán correcciones al responsable para esta fase de desembolso.</p>
                    </div>
                </div>
            @elseif ($estado === RevisionFinanciera::ESTADO_COMPLETO)
                <div role="alert" class="alert alert-success">
                    <x-heroicon-o-check-circle class="stroke-current shrink-0 h-6 w-6" />
                    <div>
                        <h3 class="font-bold">Fase completa</h3>
                        <p class="text-sm">Al marcar como completa, esta fase de desembolso quedará finalizada y se
                            avanzará a la siguiente.</p>
                    </div>
                </div>
            @endif

            {{-- Observaciones --}}
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend">Observaciones <span class="text-error">*</span></legend>
                <textarea wire:model="observaciones" id="observaciones" rows="5"
                    class="textarea w-full border-base-content/20 @error('observaciones') textarea-error @enderror"
                    placeholder="Detalle los hallazgos de la revisión financiera, documentos faltantes o correcciones necesarias..."
                    maxlength="2000"></textarea>
                @error('observaciones')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
                <p class="label text-base-content/50">{{ strlen($observaciones) }}/2000</p>
            </fieldset>

            {{-- Botones --}}
            <div class="divider"></div>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="cancelar" class="btn btn-ghost">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-accent gap-2" wire:loading.attr="disabled">
                    <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                    <x-heroicon-o-check-circle class="w-5 h-5" wire:loading.remove wire:target="guardar" />
                    Registrar Revisión
                </button>
            </div>
        </form>
    @endif
</div>
