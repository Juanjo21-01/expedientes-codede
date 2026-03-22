<?php

use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\TipoSolicitud;

new class extends Component {
    public bool $abierto = false;
    public ?int $expedienteId = null;
    public string $codigoSnip = '';
    public string $nombreProyecto = '';

    // Campos del formulario
    public $monto_contrato = '';
    public $aporte_municipalidad = '';

    public function mount(int $expedienteId)
    {
        $this->expedienteId = $expedienteId;
        $expediente = Expediente::findOrFail($expedienteId);
        $this->codigoSnip = $expediente->codigo_snip;
        $this->nombreProyecto = $expediente->nombre_proyecto;
        $this->monto_contrato = $expediente->monto_contrato ?? '';
        $this->aporte_municipalidad = $expediente->aporte_municipalidad ?? '';

        // Abrir automáticamente si falta información
        if (!$expediente->tieneInfoParaRevision()) {
            $this->abierto = true;
        }
    }

    #[Computed]
    public function tiposSolicitud()
    {
        return TipoSolicitud::ordenados()->get();
    }

    public function guardar()
    {
        $this->validate(
            [
                'monto_contrato' => 'required|numeric|min:0.01|max:999999999999.99',
                'aporte_municipalidad' => 'nullable|numeric|min:0|max:999999999999.99',
            ],
            [
                'monto_contrato.required' => 'El monto del contrato es obligatorio para iniciar la revisión.',
                'monto_contrato.numeric' => 'El monto debe ser un número válido.',
                'monto_contrato.min' => 'El monto debe ser mayor a 0.',
                'aporte_municipalidad.numeric' => 'El aporte debe ser un número válido.',
            ],
        );

        $expediente = Expediente::findOrFail($this->expedienteId);

        $expediente->update([
            'monto_contrato' => $this->monto_contrato,
            'aporte_municipalidad' => $this->aporte_municipalidad ?: null,
        ]);

        $this->abierto = false;
        $this->dispatch('info-expediente-completada');
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Información del expediente actualizada.');
    }

    public function cancelar()
    {
        // Si no tiene info, redirigir de vuelta
        $expediente = Expediente::findOrFail($this->expedienteId);
        if (!$expediente->tieneInfoParaRevision()) {
            $this->redirectRoute('expedientes.show', $expediente->id, navigate: true);
            return;
        }
        $this->abierto = false;
    }
};
?>

<div x-on:keydown.escape.window="if ($wire.abierto) $wire.cancelar()">
    <div class="modal" :class="{ 'modal-open': $wire.abierto }">
        <div class="modal-box max-w-lg">
            @if ($abierto)
                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="avatar placeholder">
                        <div
                            class="bg-warning/10 text-warning rounded-lg w-10 h-10 flex items-center justify-center border border-warning/20">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Completar Información</h3>
                        <p class="text-sm text-base-content/60">
                            Antes de iniciar la revisión financiera
                        </p>
                    </div>
                </div>

                {{-- Info del expediente --}}
                <div class="bg-base-200/70 border border-base-content/10 rounded-lg p-3 mb-4">
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Código SNIP</span>
                            <span class="font-mono font-bold">{{ $codigoSnip }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Proyecto</span>
                            <span class="font-medium text-right max-w-[60%] truncate"
                                title="{{ $nombreProyecto }}">{{ $nombreProyecto }}</span>
                        </div>
                    </div>
                </div>

                {{-- Alerta --}}
                <div role="alert" class="alert alert-warning mb-4">
                    <x-heroicon-o-exclamation-triangle class="stroke-current shrink-0 h-5 w-5" />
                    <p class="text-sm">Para iniciar la revisión financiera, es necesario registrar el <strong>monto del
                            contrato</strong> del expediente.</p>
                </div>

                <form wire:submit="guardar" class="space-y-4">
                    {{-- Monto del contrato --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Monto del Contrato (Q) <span class="text-error">*</span>
                        </legend>
                        <label
                            class="input border-base-content/20 flex items-center gap-2 @error('monto_contrato') input-error @enderror">
                            <span class="text-base-content/60 font-bold">Q</span>
                            <input type="number" wire:model="monto_contrato" step="0.01" min="0"
                                class="grow" placeholder="0.00" />
                        </label>
                        @error('monto_contrato')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Aporte municipalidad --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Aporte de la Municipalidad (Q)</legend>
                        <label
                            class="input border-base-content/20 flex items-center gap-2 @error('aporte_municipalidad') input-error @enderror">
                            <span class="text-base-content/60 font-bold">Q</span>
                            <input type="number" wire:model="aporte_municipalidad" step="0.01" min="0"
                                class="grow" placeholder="0.00" />
                        </label>
                        @error('aporte_municipalidad')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                        <p class="label text-base-content/50">Opcional</p>
                    </fieldset>

                    {{-- Botones --}}
                    <div class="modal-action">
                        <button type="button" wire:click="cancelar" class="btn btn-ghost">Cancelar</button>
                        <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                            <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                            <x-heroicon-o-check-circle class="w-4 h-4" wire:loading.remove wire:target="guardar" />
                            Guardar y Continuar
                        </button>
                    </div>
                </form>
            @endif
        </div>
        <div class="modal-backdrop" wire:click="cancelar"></div>
    </div>
</div>
