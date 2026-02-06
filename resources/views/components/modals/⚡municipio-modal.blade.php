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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                        Editar Municipio
                    </h3>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
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
