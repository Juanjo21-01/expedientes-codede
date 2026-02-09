<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Expediente;

new class extends Component {
    public bool $abierto = false;
    public ?int $expedienteId = null;
    public string $nuevoEstado = '';
    public string $observaciones = '';
    public string $estadoActual = '';
    public string $codigoSnip = '';
    public string $nombreProyecto = '';

    // Transiciones válidas por estado actual
    public function getTransicionesValidas(): array
    {
        return match ($this->estadoActual) {
            Expediente::ESTADO_RECIBIDO => [Expediente::ESTADO_EN_REVISION, Expediente::ESTADO_ARCHIVADO],
            Expediente::ESTADO_EN_REVISION => [Expediente::ESTADO_COMPLETO, Expediente::ESTADO_INCOMPLETO, Expediente::ESTADO_RECIBIDO, Expediente::ESTADO_ARCHIVADO],
            Expediente::ESTADO_COMPLETO => [Expediente::ESTADO_APROBADO, Expediente::ESTADO_RECHAZADO, Expediente::ESTADO_EN_REVISION, Expediente::ESTADO_ARCHIVADO],
            Expediente::ESTADO_INCOMPLETO => [Expediente::ESTADO_EN_REVISION, Expediente::ESTADO_RECIBIDO, Expediente::ESTADO_ARCHIVADO],
            default => [],
        };
    }

    #[On('abrir-modal-estado')]
    public function abrir(int $expedienteId)
    {
        $expediente = Expediente::findOrFail($expedienteId);

        $this->expedienteId = $expediente->id;
        $this->estadoActual = $expediente->estado;
        $this->codigoSnip = $expediente->codigo_snip;
        $this->nombreProyecto = $expediente->nombre_proyecto;
        $this->nuevoEstado = '';
        $this->observaciones = '';
        $this->abierto = true;
    }

    public function cambiarEstado()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);

        $user = auth()->user();
        if (!$user->can('cambiarEstado', $expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para cambiar el estado.');
            $this->cerrar();
            return;
        }

        $this->validate(
            [
                'nuevoEstado' => 'required|in:' . implode(',', $this->getTransicionesValidas()),
                'observaciones' => 'nullable|string|max:1000',
            ],
            [
                'nuevoEstado.required' => 'Debes seleccionar un nuevo estado.',
                'nuevoEstado.in' => 'La transición de estado seleccionada no es válida.',
            ],
        );

        $resultado = $expediente->cambiarEstado($this->nuevoEstado);

        if ($resultado) {
            // Registrar observaciones si se proporcionaron
            if ($this->observaciones) {
                $expediente->update(['observaciones' => $this->observaciones]);
            }

            $this->dispatch('expediente-estado-cambiado');
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Estado cambiado a '{$this->nuevoEstado}' exitosamente.");
        } else {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No se pudo cambiar el estado del expediente.');
        }

        $this->cerrar();
    }

    public function cerrar()
    {
        $this->abierto = false;
        $this->reset(['expedienteId', 'nuevoEstado', 'observaciones', 'estadoActual', 'codigoSnip', 'nombreProyecto']);
    }
};
?>

<div x-on:abrir-modal-estado.window="$wire.abrir($event.detail.expedienteId)">
    @if ($abierto)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-lg">
                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-info/10 text-info rounded-lg w-10 h-10 flex items-center justify-center">
                            <x-heroicon-o-arrows-right-left class="w-5 h-5" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Cambiar Estado</h3>
                        <p class="text-sm text-base-content/60">
                            <span class="font-mono">{{ $codigoSnip }}</span>
                        </p>
                    </div>
                </div>

                {{-- Estado Actual --}}
                <div class="bg-base-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-base-content/60">Estado actual:</span>
                        @php
                            $badgeClass = match ($estadoActual) {
                                Expediente::ESTADO_RECIBIDO => 'badge-info',
                                Expediente::ESTADO_EN_REVISION => 'badge-warning',
                                Expediente::ESTADO_COMPLETO => 'badge-success',
                                Expediente::ESTADO_INCOMPLETO => 'badge-error',
                                Expediente::ESTADO_APROBADO => 'badge-success',
                                Expediente::ESTADO_RECHAZADO => 'badge-error',
                                Expediente::ESTADO_ARCHIVADO => 'badge-ghost',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $estadoActual }}</span>
                    </div>
                </div>

                <form wire:submit="cambiarEstado" class="space-y-4">
                    {{-- Nuevo Estado --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Nuevo Estado <span class="text-error">*</span></legend>
                        <select wire:model="nuevoEstado" id="nuevoEstado"
                            class="select w-full @error('nuevoEstado') select-error @enderror">
                            <option value="">Seleccionar nuevo estado...</option>
                            @foreach ($this->getTransicionesValidas() as $estado)
                                <option value="{{ $estado }}">{{ $estado }}</option>
                            @endforeach
                        </select>
                        @error('nuevoEstado')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Observaciones --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Observaciones</legend>
                        <textarea wire:model="observaciones" id="observaciones_estado" rows="3"
                            class="textarea w-full @error('observaciones') textarea-error @enderror" placeholder="Razón del cambio de estado..."
                            maxlength="1000"></textarea>
                        @error('observaciones')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Botones --}}
                    <div class="modal-action">
                        <button type="button" wire:click="cerrar" class="btn btn-ghost">Cancelar</button>
                        <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                            <span wire:loading wire:target="cambiarEstado"
                                class="loading loading-spinner loading-sm"></span>
                            Cambiar Estado
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="cerrar">close</button>
            </form>
        </dialog>
    @endif
</div>
