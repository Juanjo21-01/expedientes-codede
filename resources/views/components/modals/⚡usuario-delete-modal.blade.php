<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    // Variables
    public $show = false;
    public $usuarioEliminar = null;
    public $passwordConfirm = '';

    // Abrir modal (llamado desde Alpine.js x-on)
    public function abrirModal($usuarioId)
    {
        $this->usuarioEliminar = User::find($usuarioId);
        $this->passwordConfirm = '';
        $this->resetErrorBag();
        $this->show = true;
    }

    // Eliminar usuario
    public function eliminar()
    {
        // Validar contraseña del admin
        if (!Hash::check($this->passwordConfirm, Auth::user()->password)) {
            $this->addError('passwordConfirm', 'La contraseña es incorrecta.');
            return;
        }

        try {
            // Verificar si tiene expedientes
            if ($this->usuarioEliminar->expedientes()->count() > 0) {
                $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No se puede eliminar: el usuario tiene expedientes asociados.');
                $this->cerrarModal();
                return;
            }

            // Verificar si tiene revisiones
            if ($this->usuarioEliminar->revisionesFinancieras()->count() > 0) {
                $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No se puede eliminar: el usuario tiene revisiones financieras asociadas.');
                $this->cerrarModal();
                return;
            }

            // Eliminar usuario
            $this->usuarioEliminar->municipios()->detach();
            $this->usuarioEliminar->delete();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: '¡Usuario eliminado correctamente!');
            $this->dispatch('usuario-eliminado');
            $this->cerrarModal();
        } catch (\Exception $e) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al eliminar: ' . $e->getMessage());
        }
    }

    // Cerrar modal
    public function cerrarModal()
    {
        $this->show = false;
        $this->usuarioEliminar = null;
        $this->passwordConfirm = '';
        $this->resetErrorBag();
    }

    // Limpiar error específico
    public function clearError($field)
    {
        $this->resetErrorBag($field);
    }
};
?>

<div x-on:abrir-modal-eliminar.window="$wire.abrirModal($event.detail.usuarioId)">
    @if ($show && $usuarioEliminar)
        <div class="modal modal-open">
            <div class="modal-box" wire:click.stop>
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-error/10 text-error rounded-full p-3">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-error">Eliminar Usuario</h3>
                        <p class="text-sm text-base-content/60">Esta acción no se puede deshacer</p>
                    </div>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost ml-auto">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="divider my-2"></div>

                <!-- Contenido -->
                <div class="py-2">
                    <div class="alert mb-4">
                        <x-heroicon-o-information-circle class="stroke-info shrink-0 w-6 h-6" />
                        <span>
                            ¿Estás seguro de eliminar al usuario
                            <strong class="text-error">{{ $usuarioEliminar->nombre_completo }}</strong>?
                        </span>
                    </div>

                    <form wire:submit.prevent="eliminar">
                        <fieldset class="fieldset mb-4">
                            <legend class="fieldset-legend">Ingresa tu contraseña para confirmar</legend>
                            <input type="password" wire:model="passwordConfirm"
                                wire:keydown="clearError('passwordConfirm')" placeholder="Tu contraseña"
                                class="input w-full @error('passwordConfirm') input-error @enderror" />
                            @error('passwordConfirm')
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="modal-action">
                            <button type="button" wire:click="cerrarModal" class="btn btn-ghost">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-error" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="eliminar">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </span>
                                <span wire:loading wire:target="eliminar"
                                    class="loading loading-spinner loading-sm"></span>
                                Eliminar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button wire:click="cerrarModal">close</button>
            </form>
        </div>
    @endif
</div>
