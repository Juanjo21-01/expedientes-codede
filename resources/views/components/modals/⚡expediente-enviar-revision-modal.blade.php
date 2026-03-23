<?php

use Livewire\Component;
use App\Models\Expediente;
use App\Models\Bitacora;

new class extends Component {
    public bool $abierto = false;
    public ?int $expedienteId = null;
    public string $codigoSnip = '';
    public string $nombreProyecto = '';
    public string $municipio = '';
    public string $observaciones = '';

    public function abrir(int $expedienteId)
    {
        $expediente = Expediente::with('municipio')->findOrFail($expedienteId);

        $this->expedienteId = $expediente->id;
        $this->codigoSnip = $expediente->codigo_snip;
        $this->nombreProyecto = $expediente->nombre_proyecto;
        $this->municipio = $expediente->municipio->nombre;
        $this->observaciones = '';
        $this->abierto = true;
    }

    public function enviar()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);

        $user = auth()->user();
        if (!$user->can('enviarRevision', $expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para enviar a revisión.');
            $this->cerrar();
            return;
        }

        $this->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // Cambiar estado
        $expediente->marcarEnRevision();

        // Actualizar observaciones si se proporcionaron
        if ($this->observaciones) {
            $expediente->update(['observaciones' => $this->observaciones]);
        }

        $this->dispatch('expediente-estado-cambiado');
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Expediente '{$this->codigoSnip}' enviado a revisión financiera.");
        $this->cerrar();
    }

    public function cerrar()
    {
        $this->abierto = false;
        $this->reset(['expedienteId', 'codigoSnip', 'nombreProyecto', 'municipio', 'observaciones']);
    }
};
?>

<div x-on:abrir-modal-enviar-revision.window="$wire.abrir($event.detail.expedienteId)"
    x-on:keydown.escape.window="if ($wire.abierto) $wire.cerrar()">
    <div class="modal" :class="{ 'modal-open': $wire.abierto }">
        <div class="modal-box max-w-lg">
            @if ($abierto)
                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="avatar placeholder">
                        <div
                            class="bg-primary/10 text-primary rounded-lg w-10 h-10 flex items-center justify-center border border-primary/20">
                            <x-heroicon-o-paper-airplane class="w-5 h-5" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Enviar a Revisión Financiera</h3>
                        <p class="text-sm text-base-content/60">
                            <span class="font-mono">{{ $codigoSnip }}</span>
                        </p>
                    </div>
                </div>

                {{-- Info del expediente --}}
                <div class="bg-base-200/70 border border-base-content/10 rounded-lg p-3 mb-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Proyecto</span>
                            <span class="font-medium text-right max-w-[60%] truncate"
                                title="{{ $nombreProyecto }}">{{ $nombreProyecto }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Municipio</span>
                            <span class="font-medium">{{ $municipio }}</span>
                        </div>
                    </div>
                </div>

                {{-- Alerta informativa --}}
                <div role="alert" class="alert alert-info mb-4">
                    <x-heroicon-o-information-circle class="stroke-current shrink-0 h-5 w-5" />
                    <p class="text-sm">Al enviar a revisión, el expediente pasará al área financiera para su evaluación.
                        El estado cambiará a <strong>En Revisión</strong>.</p>
                </div>

                <form wire:submit="enviar" class="space-y-4">
                    {{-- Observaciones opcionales --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Observaciones</legend>
                        <textarea wire:model="observaciones" rows="3"
                            class="textarea w-full border-base-content/20 @error('observaciones') textarea-error @enderror"
                            placeholder="Notas adicionales para el revisor financiero..." maxlength="1000"></textarea>
                        @error('observaciones')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Botones --}}
                    <div class="modal-action">
                        <button type="button" wire:click="cerrar" class="btn btn-ghost">Cancelar</button>
                        <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                            <span wire:loading wire:target="enviar" class="loading loading-spinner loading-sm"></span>
                            <x-heroicon-o-paper-airplane class="w-4 h-4" wire:loading.remove wire:target="enviar" />
                            Enviar a Revisión
                        </button>
                    </div>
                </form>
            @endif
        </div>
        <div class="modal-backdrop" wire:click="cerrar"></div>
    </div>
</div>
