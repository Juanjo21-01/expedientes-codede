<?php

use Livewire\Component;
use App\Models\Expediente;
use App\Models\RevisionFinanciera;

new class extends Component {
    public int $expedienteId;

    // Campos del formulario
    public string $estado = '';
    public string $accion = '';
    public string $monto_aprobado = '';
    public string $observaciones = '';

    public function mount(int $expedienteId)
    {
        $this->expedienteId = $expedienteId;
    }

    // Resetear acción cuando cambia el estado
    public function updatedEstado()
    {
        // Si es Incompleto, la acción típica es Solicitar Correcciones
        if ($this->estado === RevisionFinanciera::ESTADO_INCOMPLETO) {
            $this->accion = RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES;
            $this->monto_aprobado = '';
        }
    }

    public function guardar()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);

        // Verificar autorización
        $user = auth()->user();
        if (!$user->can('revisarFinanciera', $expediente)) {
            session()->flash('error', 'No tienes permiso para registrar revisiones financieras.');
            return;
        }

        $rules = [
            'estado' => 'required|in:' . implode(',', RevisionFinanciera::getEstados()),
            'observaciones' => 'required|string|max:2000',
        ];

        // Solo validar acción si se seleccionó
        if ($this->accion) {
            $rules['accion'] = 'in:' . implode(',', RevisionFinanciera::getAcciones());
        }

        // Monto aprobado requerido si la acción es Aprobar
        if ($this->accion === RevisionFinanciera::ACCION_APROBAR) {
            $rules['monto_aprobado'] = 'required|numeric|min:0.01';
        } elseif ($this->monto_aprobado !== '') {
            $rules['monto_aprobado'] = 'numeric|min:0';
        }

        $validated = $this->validate($rules, [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'observaciones.required' => 'Las observaciones son obligatorias.',
            'observaciones.max' => 'Las observaciones no pueden exceder 2000 caracteres.',
            'monto_aprobado.required' => 'El monto aprobado es obligatorio cuando la acción es Aprobar.',
            'monto_aprobado.numeric' => 'El monto debe ser un número válido.',
            'monto_aprobado.min' => 'El monto debe ser mayor a 0.',
        ]);

        // Crear la revisión
        $revision = RevisionFinanciera::create([
            'expediente_id' => $expediente->id,
            'revisor_id' => $user->id,
            'estado' => $this->estado,
            'accion' => $this->accion ?: null,
            'monto_aprobado' => $this->monto_aprobado !== '' ? $this->monto_aprobado : null,
            'observaciones' => $this->observaciones,
            'fecha_revision' => now(),
        ]);

        // Actualizar estado del expediente según la revisión
        if ($this->estado === RevisionFinanciera::ESTADO_COMPLETO) {
            $expediente->marcarCompleto();
        } elseif ($this->estado === RevisionFinanciera::ESTADO_INCOMPLETO) {
            $expediente->marcarIncompleto();
        }

        // Si la acción es Aprobar, aprobar el expediente
        if ($this->accion === RevisionFinanciera::ACCION_APROBAR) {
            $expediente->aprobar();
        } elseif ($this->accion === RevisionFinanciera::ACCION_RECHAZAR) {
            $expediente->rechazar();
        }

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
    <form wire:submit="guardar" class="space-y-6">
        {{-- Estado de la Revisión --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend">Estado de la documentación <span class="text-error">*</span></legend>
                <select wire:model.live="estado" id="estado"
                    class="select w-full @error('estado') select-error @enderror">
                    <option value="">Seleccionar estado...</option>
                    @foreach (RevisionFinanciera::getEstados() as $est)
                        <option value="{{ $est }}">{{ $est }}</option>
                    @endforeach
                </select>
                @error('estado')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
                <p class="label text-base-content/50">
                    ¿La documentación financiera está completa?
                </p>
            </fieldset>

            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend">Acción</legend>
                <select wire:model.live="accion" id="accion"
                    class="select w-full @error('accion') select-error @enderror">
                    <option value="">Sin acción definitiva</option>
                    @foreach (RevisionFinanciera::getAcciones() as $acc)
                        @php
                            $label = match ($acc) {
                                RevisionFinanciera::ACCION_APROBAR => '✅ Aprobar expediente',
                                RevisionFinanciera::ACCION_RECHAZAR => '❌ Rechazar expediente',
                                RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES => '⚠️ Solicitar correcciones',
                                default => $acc,
                            };
                        @endphp
                        <option value="{{ $acc }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('accion')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
                <p class="label text-base-content/50">
                    Opcional: acción definitiva sobre el expediente
                </p>
            </fieldset>
        </div>

        {{-- Alert contextual --}}
        @if ($accion === RevisionFinanciera::ACCION_APROBAR)
            <div role="alert" class="alert alert-success">
                <x-heroicon-o-check-circle class="stroke-current shrink-0 h-6 w-6" />
                <div>
                    <h3 class="font-bold">Aprobar expediente</h3>
                    <p class="text-sm">Al aprobar, el expediente cambiará a estado <strong>Aprobado</strong> y se
                        registrará la fecha de aprobación. Esta acción es definitiva.</p>
                </div>
            </div>
        @elseif ($accion === RevisionFinanciera::ACCION_RECHAZAR)
            <div role="alert" class="alert alert-error">
                <x-heroicon-o-x-circle class="stroke-current shrink-0 h-6 w-6" />
                <div>
                    <h3 class="font-bold">Rechazar expediente</h3>
                    <p class="text-sm">Al rechazar, el expediente cambiará a estado <strong>Rechazado</strong>. Esta
                        acción es definitiva.</p>
                </div>
            </div>
        @elseif ($accion === RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES)
            <div role="alert" class="alert alert-warning">
                <x-heroicon-o-exclamation-triangle class="stroke-current shrink-0 h-6 w-6" />
                <div>
                    <h3 class="font-bold">Solicitar correcciones</h3>
                    <p class="text-sm">El expediente quedará marcado como <strong>Incompleto</strong> y el responsable
                        será notificado para realizar correcciones.</p>
                </div>
            </div>
        @endif

        {{-- Monto aprobado (visible cuando la acción es Aprobar) --}}
        @if ($accion === RevisionFinanciera::ACCION_APROBAR)
            <fieldset class="fieldset w-full max-w-md">
                <legend class="fieldset-legend">Monto Aprobado <span class="text-error">*</span></legend>
                <label class="input flex items-center gap-2 @error('monto_aprobado') input-error @enderror">
                    <span class="text-base-content/60 font-bold">Q</span>
                    <input type="number" wire:model="monto_aprobado" id="monto_aprobado" step="0.01" min="0"
                        class="grow" placeholder="0.00" />
                </label>
                @error('monto_aprobado')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        @endif

        {{-- Observaciones --}}
        <fieldset class="fieldset w-full">
            <legend class="fieldset-legend">Observaciones <span class="text-error">*</span></legend>
            <textarea wire:model="observaciones" id="observaciones" rows="5"
                class="textarea w-full @error('observaciones') textarea-error @enderror"
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
</div>
