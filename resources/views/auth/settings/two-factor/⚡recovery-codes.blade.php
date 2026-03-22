<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Error al cargar los códigos de recuperación');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div class="space-y-6 rounded-xl border border-base-content/10 bg-base-100/70 py-6 shadow-sm" wire:cloak
    x-data="{ showRecoveryCodes: false }">
    <div class="px-6 space-y-2">
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-semibold text-base-content">Códigos de recuperación 2FA</h3>
        </div>
        <p class="text-sm text-base-content/70">
            Los códigos de recuperación te permiten acceder a tu cuenta si pierdes tu dispositivo 2FA.
            Guárdalos en un lugar seguro como un administrador de contraseñas.
        </p>
    </div>

    <div class="px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button x-show="!showRecoveryCodes" class="btn btn-primary" @click="showRecoveryCodes = true;"
                aria-expanded="false" aria-controls="recovery-codes-section">
                Ver códigos de recuperación
            </button>

            <button x-show="showRecoveryCodes" class="btn btn-outline" @click="showRecoveryCodes = false"
                aria-expanded="true" aria-controls="recovery-codes-section">
                Ocultar códigos de recuperación
            </button>

            @if (filled($recoveryCodes))
                <button x-show="showRecoveryCodes" class="btn btn-warning" wire:click="regenerateRecoveryCodes">
                    Regenerar códigos
                </button>
            @endif
        </div>

        <div x-show="showRecoveryCodes" x-transition id="recovery-codes-section" class="relative overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes">
            <div class="mt-3 space-y-3">
                @error('recoveryCodes')
                    <div class="alert alert-error border border-error/20 text-sm shadow-sm"><span>{{ $message }}</span>
                    </div>
                @enderror

                @if (filled($recoveryCodes))
                    <div class="grid gap-1 p-4 font-mono text-sm rounded-lg bg-base-200" role="list"
                        aria-label="Códigos de recuperación">
                        @foreach ($recoveryCodes as $code)
                            <div role="listitem" class="select-text" wire:loading.class="opacity-50 animate-pulse">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-base-content/70">
                        Cada código de recuperación solo puede usarse una vez para acceder a tu cuenta y se eliminará
                        después de su uso.
                        Si necesitas más, haz clic en Regenerar códigos.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
