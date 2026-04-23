<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Acceso denegado.');
        }

        if ($user->isAdmin()) {
            $this->addError('deleteUser', 'La cuenta Administrador no puede eliminarse.');
            return;
        }

        if ($user->tieneInformacionAsociada()) {
            $this->addError('deleteUser', 'No puedes eliminar tu cuenta porque ya tiene información asociada en el sistema.');
            return;
        }

        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h3 class="text-lg font-semibold text-base-content">Eliminar cuenta</h3>
        <p class="text-sm text-base-content/70">Elimina tu cuenta y todos sus recursos permanentemente</p>
    </div>

    <button class="btn btn-error" onclick="confirm_user_deletion.showModal()" data-test="delete-user-button">
        Eliminar cuenta
    </button>

    <dialog id="confirm_user_deletion" class="modal" @if ($errors->isNotEmpty()) open @endif>
        <div class="modal-box max-w-lg">
            <form method="POST" wire:submit="deleteUser" class="space-y-6">
                <div>
                    <h4 class="text-lg font-semibold text-base-content">
                        ¿Estás seguro de que deseas eliminar tu cuenta?</h4>
                    <p class="mt-2 text-sm text-base-content/70">
                        Una vez eliminada tu cuenta, todos sus recursos y datos se borrarán permanentemente.
                        Ingresa tu contraseña para confirmar que deseas eliminar tu cuenta.
                    </p>
                </div>

                @error('deleteUser')
                    <div class="alert alert-error border border-error/30">
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Contraseña</legend>
                    <input id="delete_password" wire:model="password" type="password"
                        class="input input-bordered w-full border-base-content/20" />
                    @error('password')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="modal-action">
                    <button type="button" class="btn" onclick="confirm_user_deletion.close()">Cancelar</button>
                    <button class="btn btn-error" type="submit" data-test="confirm-delete-user-button">
                        Eliminar cuenta
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>Cerrar</button>
        </form>
    </dialog>
</section>
