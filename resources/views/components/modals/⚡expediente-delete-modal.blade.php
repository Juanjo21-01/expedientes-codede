<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Expediente;

new class extends Component {
    public bool $abierto = false;
    public ?int $expedienteId = null;
    public string $codigoSnip = '';
    public string $nombreProyecto = '';
    public string $estado = '';
    public bool $tieneRevisiones = false;
    public bool $puedeEliminar = false;

    #[On('abrir-modal-eliminar')]
    public function abrir(int $expedienteId)
    {
        $expediente = Expediente::withCount('revisionesFinancieras')->findOrFail($expedienteId);

        $this->expedienteId = $expediente->id;
        $this->codigoSnip = $expediente->codigo_snip;
        $this->nombreProyecto = $expediente->nombre_proyecto;
        $this->estado = $expediente->estado;
        $this->tieneRevisiones = $expediente->revisiones_financieras_count > 0;

        // Solo se puede eliminar si: está en Recibido Y no tiene revisiones
        $this->puedeEliminar = $expediente->estaRecibido() && !$this->tieneRevisiones;
        $this->abierto = true;
    }

    public function eliminar()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);
        $user = auth()->user();

        if (!$user->can('delete', $expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para eliminar este expediente.');
            $this->cerrar();
            return;
        }

        $expediente->delete();

        $this->dispatch('expediente-eliminado');
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Expediente eliminado permanentemente.');
        $this->cerrar();
    }

    public function archivar()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);
        $user = auth()->user();

        if (!$user->can('archivar', $expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para archivar este expediente.');
            $this->cerrar();
            return;
        }

        $expediente->archivar();

        $this->dispatch('expediente-estado-cambiado');
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Expediente archivado correctamente.');
        $this->cerrar();
    }

    public function cerrar()
    {
        $this->abierto = false;
        $this->reset(['expedienteId', 'codigoSnip', 'nombreProyecto', 'estado', 'tieneRevisiones', 'puedeEliminar']);
    }
};
?>

<div x-on:abrir-modal-eliminar.window="$wire.abrir($event.detail.expedienteId)">
    @if ($abierto)
        <div class="modal modal-open">
            <div class="modal-box max-w-lg">
                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-error/10 text-error rounded-lg w-10 h-10 flex items-center justify-center">
                            <x-heroicon-o-trash class="w-5 h-5" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">
                            {{ $puedeEliminar ? 'Eliminar Expediente' : 'Archivar Expediente' }}
                        </h3>
                        <p class="text-sm text-base-content/60">
                            <span class="font-mono">Código: {{ $codigoSnip }}</span>
                        </p>
                    </div>
                </div>

                {{-- Info del expediente --}}
                <div class="bg-base-200 rounded-lg p-3 mb-4">
                    <p class="text-sm font-medium">{{ $nombreProyecto }}</p>
                    <p class="text-xs text-base-content/60 mt-1">
                        Estado: <span class="font-medium badge">{{ $estado }}</span>
                        @if ($tieneRevisiones)
                            · Tiene revisiones financieras
                        @endif
                    </p>
                </div>

                @if ($puedeEliminar)
                    {{-- Puede eliminar: está en Recibido y no tiene revisiones --}}
                    <div role="alert" class="alert alert-warning mb-4">
                        <x-heroicon-o-exclamation-triangle class="stroke-current shrink-0 h-6 w-6" />
                        <div>
                            <p class="text-sm">Puedes <strong>eliminar permanentemente</strong> este expediente porque
                                aún está en estado Recibido y no tiene revisiones. También puedes optar por
                                <strong>archivar</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="modal-action flex-col sm:flex-row gap-2">
                        <button type="button" wire:click="cerrar" class="btn btn-ghost flex-1">Cancelar</button>
                        <button type="button" wire:click="archivar" class="btn btn-warning flex-1 gap-2"
                            wire:loading.attr="disabled">
                            <span wire:loading wire:target="archivar" class="loading loading-spinner loading-sm"></span>
                            <x-heroicon-o-archive-box-arrow-down class="w-4 h-4" wire:loading.remove
                                wire:target="archivar" />
                            Archivar
                        </button>
                        <button type="button" wire:click="eliminar"
                            wire:confirm="¿Estás seguro? Esta acción NO se puede deshacer."
                            class="btn btn-error flex-1 gap-2" wire:loading.attr="disabled">
                            <span wire:loading wire:target="eliminar" class="loading loading-spinner loading-sm"></span>
                            <x-heroicon-o-trash class="w-4 h-4" wire:loading.remove wire:target="eliminar" />
                            Eliminar
                        </button>
                    </div>
                @else
                    {{-- No puede eliminar: tiene revisiones o no está en Recibido → solo archivar --}}
                    <div role="alert" class="alert alert-info mb-4">
                        <x-heroicon-o-information-circle class="stroke-current shrink-0 w-6 h-6" />
                        <div>
                            <p class="text-sm">Este expediente
                                <strong>no puede ser eliminado</strong> porque
                                @if ($tieneRevisiones)
                                    ya tiene revisiones financieras registradas.
                                @else
                                    no se encuentra en estado Recibido.
                                @endif
                                Solo es posible <strong>archivarlo</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="modal-action">
                        <button type="button" wire:click="cerrar" class="btn btn-ghost">Cancelar</button>
                        <button type="button" wire:click="archivar" class="btn btn-warning gap-2"
                            wire:loading.attr="disabled">
                            <span wire:loading wire:target="archivar" class="loading loading-spinner loading-sm"></span>
                            <x-heroicon-o-archive-box-arrow-down class="w-4 h-4" wire:loading.remove
                                wire:target="archivar" />
                            Archivar Expediente
                        </button>
                    </div>
                @endif
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="cerrar">close</button>
            </form>
        </div>
    @endif
</div>
