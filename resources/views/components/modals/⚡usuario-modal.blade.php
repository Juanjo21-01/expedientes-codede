<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    // Variables
    public $show = false;
    public $usuarioId = null;

    // Escuchar evento para crear (sin ID)
    #[On('crear-usuario')]
    public function crearUsuario()
    {
        $this->usuarioId = null;
        $this->show = true;
    }

    // Escuchar evento para abrir modal con ID (editar)
    #[On('abrir-modal-usuario')]
    public function abrirModal($usuarioId = null)
    {
        $this->usuarioId = $usuarioId;
        $this->show = true;
    }

    // Escuchar evento para cerrar modal
    #[On('cerrar-modal-usuario')]
    public function cerrarModal()
    {
        $this->show = false;
        $this->usuarioId = null;
    }
};
?>

<div>
    @if ($show)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-2xl" wire:click.stop>
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3
                        class="font-bold text-lg flex items-center gap-2 {{ $usuarioId ? 'text-warning' : 'text-primary' }}">
                        @if ($usuarioId)
                            <x-heroicon-o-pencil-square class="w-6 h-6" />
                            Editar Usuario
                        @else
                            <x-heroicon-o-user-plus class="w-6 h-6" />
                            Nuevo Usuario
                        @endif
                    </h3>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="divider my-0"></div>

                <!-- Formulario -->
                <livewire:forms.usuario-form :usuarioId="$usuarioId" :key="'usuario-form-' . ($usuarioId ?? 'new')" />
            </div>
            <form method="dialog" class="modal-backdrop">
                <button wire:click="cerrarModal">close</button>
            </form>
        </div>
    @endif
</div>
