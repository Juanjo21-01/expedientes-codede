<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    // Variables
    public $show = false;
    public $municipioId = null;

    // Escuchar evento para abrir modal con ID (editar)
    #[On('abrir-modal-municipio')]
    public function abrirModal($municipioId)
    {
        $this->municipioId = $municipioId;
        $this->show = true;
    }

    // Escuchar evento para cerrar modal
    #[On('cerrar-modal-municipio')]
    public function cerrarModal()
    {
        $this->show = false;
        $this->municipioId = null;
    }
};
?>

<div>
    @if ($show)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-xl" wire:click.stop>
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg flex items-center gap-2 text-warning">
                        <x-heroicon-o-pencil-square class="w-6 h-6" />
                        Editar Municipio
                    </h3>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="divider my-0"></div>

                <!-- Formulario -->
                <livewire:forms.municipio-form :municipioId="$municipioId" :key="'municipio-form-' . $municipioId" />
            </div>
            <form method="dialog" class="modal-backdrop">
                <button wire:click="cerrarModal">close</button>
            </form>
        </div>
    @endif
</div>
