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
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Contraseña actual <span class="text-error">*</span></legend>
                <input id="current_password" wire:model="current_password" type="password" required
                    autocomplete="current-password" class="input w-full" />
                @error('current_password')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nueva contraseña <span class="text-error">*</span></legend>
                <input id="password" wire:model="password" type="password" required autocomplete="new-password"
                    class="input w-full" />
                @error('password')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Confirmar contraseña <span class="text-error">*</span></legend>
                <input id="password_confirmation" wire:model="password_confirmation" type="password" required
                    autocomplete="new-password" class="input w-full" />
            </fieldset>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-primary w-full" data-test="update-password-button">
                        Guardar
                    </button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    Guardado.
                </x-action-message>
            </div>
        </form>
    </x-auth.settings.layout>
</section>
