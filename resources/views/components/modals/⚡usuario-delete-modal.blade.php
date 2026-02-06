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

    // Escuchar evento para abrir modal
    #[On('abrir-modal-eliminar')]
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

<div>
    @if ($show && $usuarioEliminar)
        <div class="modal modal-open">
            <div class="modal-box" wire:click.stop>
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-error/10 text-error rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-error">Eliminar Usuario</h3>
                        <p class="text-sm text-base-content/60">Esta acción no se puede deshacer</p>
                    </div>
                    <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost ml-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="divider my-2"></div>

                <!-- Contenido -->
                <div class="py-2">
                    <div class="alert mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="stroke-info shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>
                            ¿Estás seguro de eliminar al usuario
                            <strong class="text-error">{{ $usuarioEliminar->nombre_completo }}</strong>?
                        </span>
                    </div>

                    <form wire:submit.prevent="eliminar">
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Ingresa tu contraseña para confirmar</span>
                            </label>
                            <input type="password" wire:model="passwordConfirm"
                                wire:keydown="clearError('passwordConfirm')" placeholder="Tu contraseña"
                                class="input input-bordered w-full @error('passwordConfirm') input-error @enderror" />
                            @error('passwordConfirm')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="modal-action">
                            <button type="button" wire:click="cerrarModal" class="btn btn-ghost">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-error" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
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
