<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    // Variables
    public $show = false;
    public $usuarioId = null;

    // Crear usuario (sin ID)
    public function crearUsuario()
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Acceso Denegado');
        }

        $this->usuarioId = null;
        $this->show = true;
    }

    // Abrir modal con ID (editar)
    public function abrirModal($usuarioId = null)
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Acceso Denegado');
        }

        $this->usuarioId = $usuarioId;
        $this->show = true;
    }

    // Cerrar modal (escucha evento Livewire del formulario hijo)
    #[On('cerrar-modal-usuario')]
    public function cerrarModal()
    {
        $this->show = false;
        $this->usuarioId = null;
    }
};
?>

<div x-on:crear-usuario.window="$wire.crearUsuario()"
    x-on:abrir-modal-usuario.window="$wire.abrirModal($event.detail.usuarioId)"
    x-on:keydown.escape.window="if ($wire.show) $wire.cerrarModal()">
    <div class="modal" :class="{ 'modal-open': $wire.show }">
        <div class="modal-box w-11/12 max-w-2xl" wire:click.stop>
            @if ($show)
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div
                                class="rounded-lg w-10 h-10 flex items-center justify-center {{ $usuarioId ? 'bg-warning/10 text-warning' : 'bg-primary/10 text-primary' }}">
                                @if ($usuarioId)
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                @else
                                    <x-heroicon-o-user-plus class="w-5 h-5" />
                                @endif
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">{{ $usuarioId ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
                            <p class="text-xs text-base-content/50">
                                {{ $usuarioId ? 'Actualiza datos y asignaciones del usuario' : 'Crea un nuevo usuario en el sistema' }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="divider my-0"></div>

                <!-- Formulario -->
                <livewire:forms.usuario-form :usuarioId="$usuarioId" :key="'usuario-form-' . ($usuarioId ?? 'new')" />
            @endif
        </div>
        <div class="modal-backdrop" wire:click="cerrarModal"></div>
    </div>
</div>
