<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    <h2 class="sr-only">Configuración de contraseña</h2>

    <x-auth.settings.layout heading="Contraseña"
        subheading="Asegúrate de usar una contraseña larga y segura para mantener tu cuenta protegida">
        <form method="POST" wire:submit="updatePassword" class="mt-1 space-y-6">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Contraseña actual <span class="text-error">*</span></legend>
                <input id="current_password" wire:model="current_password" type="password" required
                    autocomplete="current-password" class="input input-bordered w-full border-base-content/20" />
                @error('current_password')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nueva contraseña <span class="text-error">*</span></legend>
                <input id="password" wire:model="password" type="password" required autocomplete="new-password"
                    class="input input-bordered w-full border-base-content/20" />
                @error('password')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Confirmar contraseña <span class="text-error">*</span></legend>
                <input id="password_confirmation" wire:model="password_confirmation" type="password" required
                    autocomplete="new-password" class="input input-bordered w-full border-base-content/20" />
            </fieldset>

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="btn btn-primary" data-test="update-password-button">
                    Guardar
                </button>

                <x-action-message class="me-3" on="password-updated">
                    Guardado.
                </x-action-message>
            </div>
        </form>
    </x-auth.settings.layout>
</section>
