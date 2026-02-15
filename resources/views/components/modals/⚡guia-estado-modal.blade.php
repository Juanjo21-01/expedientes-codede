<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Guia;

new class extends Component {
    public bool $mostrar = false;
    public ?int $guiaId = null;
    public string $tituloGuia = '';
    public string $categoriaGuia = '';
    public int $versionGuia = 0;
    public bool $estadoActual = false;

    #[On('abrir-estado-modal')]
    public function abrir(int $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $this->authorize('toggleEstado', $guia);

        $this->guiaId = $guia->id;
        $this->tituloGuia = $guia->titulo;
        $this->categoriaGuia = $guia->categoria;
        $this->versionGuia = $guia->version;
        $this->estadoActual = $guia->estado;
        $this->mostrar = true;
    }

    public function confirmar()
    {
        $guia = Guia::findOrFail($this->guiaId);
        $this->authorize('toggleEstado', $guia);

        if (!$guia->estado) {
            // Al activar, desactivar las demás de la misma categoría
            Guia::desactivarCategoria($guia->categoria);
        }

        $guia->estado = !$guia->estado;
        $guia->save();

        $mensaje = $guia->estado ? 'Guía activada exitosamente.' : 'Guía desactivada exitosamente.';

        $this->cerrar();
        $this->dispatch('guia-estado-cambiado');
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: $mensaje);
    }

    public function cerrar()
    {
        $this->mostrar = false;
        $this->reset(['guiaId', 'tituloGuia', 'categoriaGuia', 'versionGuia', 'estadoActual']);
    }
};
?>

<div>
    @if ($mostrar)
        <div class="modal modal-open">
            <div class="modal-box max-w-md" wire:click.stop>
                {{-- Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="bg-{{ $estadoActual ? 'warning' : 'success' }}/10 text-{{ $estadoActual ? 'warning' : 'success' }} rounded-lg w-10 h-10 flex items-center justify-center">
                        @if ($estadoActual)
                            <x-heroicon-o-eye-slash class="w-5 h-5" />
                        @else
                            <x-heroicon-o-check-circle class="w-5 h-5" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ $estadoActual ? 'Desactivar' : 'Activar' }} Guía</h3>
                        <p class="text-sm text-base-content/60">Confirme el cambio de estado</p>
                    </div>
                </div>

                {{-- Info de la guía --}}
                <div class="bg-base-200 rounded-lg p-4 space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Título:</span>
                        <span class="font-medium">{{ $tituloGuia }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Categoría:</span>
                        <span class="font-medium">{{ $categoriaGuia }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Versión:</span>
                        <span class="font-mono">v{{ $versionGuia }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Estado actual:</span>
                        @if ($estadoActual)
                            <span class="badge badge-success badge-sm">Activo</span>
                        @else
                            <span class="badge badge-ghost badge-sm">Inactivo</span>
                        @endif
                    </div>
                </div>

                @if (!$estadoActual)
                    <div role="alert" class="alert alert-info mb-4">
                        <x-heroicon-o-information-circle class="stroke-current shrink-0 w-5 h-5" />
                        <span class="text-xs">Al activar esta guía, la versión activa actual de la categoría
                            "{{ $categoriaGuia }}" será desactivada automáticamente.</span>
                    </div>
                @endif

                {{-- Botones --}}
                <div class="modal-action">
                    <button type="button" wire:click="cerrar" class="btn btn-ghost">Cancelar</button>
                    <button type="button" wire:click="confirmar"
                        class="btn {{ $estadoActual ? 'btn-warning' : 'btn-success' }} gap-2"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmar" class="loading loading-spinner loading-sm"></span>
                        @if ($estadoActual)
                            <x-heroicon-o-eye-slash class="w-4 h-4" wire:loading.remove wire:target="confirmar" />
                            Desactivar
                        @else
                            <x-heroicon-o-check-circle class="w-4 h-4" wire:loading.remove wire:target="confirmar" />
                            Activar
                        @endif
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cerrar"></div>
        </div>
    @endif
</div>
