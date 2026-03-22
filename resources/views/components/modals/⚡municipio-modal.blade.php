<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    // Variables
    public $show = false;
    public $municipioId = null;

    // Abrir modal con ID (editar)
    public function abrirModal($municipioId)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->municipioId = $municipioId;
        $this->show = true;
    }

    // Cerrar modal (escucha evento Livewire del formulario hijo)
    #[On('cerrar-modal-municipio')]
    public function cerrarModal()
    {
        $this->show = false;
        $this->municipioId = null;
    }
};
?>

<div x-on:abrir-modal-municipio.window="$wire.abrirModal($event.detail.municipioId)"
    x-on:keydown.escape.window="if ($wire.show) $wire.cerrarModal()">
    <div class="modal" :class="{ 'modal-open': $wire.show }">
        <div class="modal-box w-11/12 max-w-xl" wire:click.stop>
            @if ($show)
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div
                                class="bg-warning/10 text-warning rounded-lg w-10 h-10 flex items-center justify-center">
                                <x-heroicon-o-pencil-square class="w-5 h-5" />
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Editar Municipio</h3>
                            <p class="text-xs text-base-content/50">Actualiza datos de contacto y observaciones</p>
                        </div>
                    </div>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="divider my-0"></div>

                <!-- Formulario -->
                <livewire:forms.municipio-form :municipioId="$municipioId" :key="'municipio-form-' . $municipioId" />
            @endif
        </div>
        <div class="modal-backdrop" wire:click="cerrarModal"></div>
    </div>
</div>
