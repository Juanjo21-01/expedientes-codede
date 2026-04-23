<?php

use Livewire\Component;
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
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Acceso Denegado');
        }

        $this->usuarioEliminar = User::find($usuarioId);

        if (!$this->usuarioEliminar) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Usuario no encontrado.');
            return;
        }

        if (!$this->usuarioEliminar->puedeSerEliminado()) {
            $mensaje = $this->usuarioEliminar->isAdmin() ? 'No se puede eliminar: la cuenta Administrador está protegida.' : 'No se puede eliminar: el usuario tiene información asociada en el sistema.';

            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: $mensaje);
            $this->cerrarModal();
            return;
        }

        $this->passwordConfirm = '';
        $this->resetErrorBag();
        $this->show = true;
    }

    // Eliminar usuario
    public function eliminar()
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Acceso Denegado');
        }

        if (!$this->usuarioEliminar instanceof User) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Usuario no válido para eliminar.');
            $this->cerrarModal();
            return;
        }

        $this->usuarioEliminar->refresh();

        if (!$this->usuarioEliminar->puedeSerEliminado()) {
            $mensaje = $this->usuarioEliminar->isAdmin() ? 'No se puede eliminar: la cuenta Administrador está protegida.' : 'No se puede eliminar: el usuario tiene información asociada en el sistema.';

            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: $mensaje);
            $this->cerrarModal();
            return;
        }

        // Validar contraseña del admin
        if (!Hash::check($this->passwordConfirm, Auth::user()->password)) {
            $this->addError('passwordConfirm', 'La contraseña es incorrecta.');
            return;
        }

        try {
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

<div x-on:abrir-modal-eliminar.window="$wire.abrirModal($event.detail.usuarioId)"
    x-on:keydown.escape.window="if ($wire.show) $wire.cerrarModal()">
    <div class="modal" :class="{ 'modal-open': $wire.show }">
        <div class="modal-box" wire:click.stop>
            @if ($show && $usuarioEliminar)
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-error/10 text-error rounded-lg p-2.5">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-error">Eliminar Usuario</h3>
                        <p class="text-sm text-base-content/60">Esta acción no se puede deshacer</p>
                    </div>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost ml-auto">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="divider my-2"></div>

                <!-- Contenido -->
                <div class="py-2">
                    <div class="alert alert-warning mb-4">
                        <x-heroicon-o-information-circle class="stroke-info shrink-0 w-6 h-6" />
                        <span>
                            ¿Estás seguro de eliminar al usuario
                            <strong class="text-error">{{ $usuarioEliminar->nombre_completo }}</strong>?
                        </span>
                    </div>

                    <form wire:submit.prevent="eliminar">
                        <fieldset class="fieldset mb-4">
                            <legend class="fieldset-legend text-xs uppercase tracking-wide">Ingresa tu contraseña para
                                confirmar</legend>
                            <input type="password" wire:model="passwordConfirm"
                                wire:keydown="clearError('passwordConfirm')" placeholder="Tu contraseña"
                                class="input input-bordered w-full @error('passwordConfirm') input-error @enderror" />
                            @error('passwordConfirm')
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="modal-action">
                            <button type="button" wire:click="cerrarModal" class="btn btn-ghost btn-sm">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-error btn-sm" wire:loading.attr="disabled">
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
            @endif
        </div>
        <div class="modal-backdrop" wire:click="cerrarModal"></div>
    </div>
</div>
