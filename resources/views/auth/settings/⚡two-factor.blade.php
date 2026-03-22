<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

new class extends Component {
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(auth()->user());

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();
        $this->showModal = true;
    }

    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Error al obtener los datos de configuración.');
            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;
            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->closeModal();
        $this->twoFactorEnabled = true;
    }

    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');
        $this->resetErrorBag();
    }

    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    public function closeModal(): void
    {
        $this->reset('code', 'manualSetupKey', 'qrCodeSvg', 'showModal', 'showVerificationStep');

        $this->resetErrorBag();

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }
    }

    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => 'Autenticación en dos pasos habilitada',
                'description' => 'La autenticación en dos pasos está ahora habilitada. Escanea el código QR o ingresa la clave de configuración en tu aplicación autenticadora.',
                'buttonText' => 'Cerrar',
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => 'Verificar código de autenticación',
                'description' => 'Ingresa el código de 6 dígitos de tu aplicación autenticadora.',
                'buttonText' => 'Continuar',
            ];
        }

        return [
            'title' => 'Habilitar autenticación en dos pasos',
            'description' => 'Para finalizar la habilitación, escanea el código QR o ingresa la clave de configuración en tu aplicación autenticadora.',
            'buttonText' => 'Continuar',
        ];
    }
}; ?>

<section class="w-full">
    <h2 class="sr-only">Configuración de autenticación en dos pasos</h2>

    <x-auth.settings.layout heading="Autenticación en dos pasos"
        subheading="Administra la configuración de autenticación en dos pasos">
        <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="space-y-4">
                    <div class="badge badge-success badge-soft badge-outline">Habilitada</div>

                    <p class="text-sm text-base-content/70">
                        Con la autenticación en dos pasos habilitada, se te pedirá un código seguro durante el inicio de
                        sesión,
                        el cual puedes obtener de la aplicación autenticadora (TOTP) instalada en tu teléfono.
                    </p>

                    @livewire('auth::settings.two-factor.recovery-codes')

                    <div class="flex justify-start">
                        <button class="btn btn-error" wire:click="disable">
                            Deshabilitar 2FA
                        </button>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div class="badge badge-warning badge-soft badge-outline">Deshabilitada</div>

                    <p class="text-sm text-base-content/70">
                        Cuando habilites la autenticación en dos pasos, se te pedirá un código seguro durante el inicio
                        de sesión.
                        Este código se puede obtener de una aplicación autenticadora (TOTP) en tu teléfono.
                    </p>

                    <button class="btn btn-primary" wire:click="enable">
                        Habilitar 2FA
                    </button>
                </div>
            @endif
        </div>
    </x-auth.settings.layout>

    <dialog class="modal" @if ($showModal) open @endif>
        <div class="modal-box max-w-md">
            <div class="space-y-6">
                <div class="space-y-2 text-center">
                    <h3 class="text-lg font-semibold">{{ $this->modalConfig['title'] }}</h3>
                    <p class="text-sm text-base-content/70">{{ $this->modalConfig['description'] }}</p>
                </div>

                @if ($showVerificationStep)
                    <div class="space-y-4" x-data="{ otp: @entangle('code').live }">
                        <input type="text" wire:model.live="code" x-model="otp" maxlength="6" inputmode="numeric"
                            pattern="[0-9]*"
                            class="input input-bordered w-full border-base-content/20 text-center tracking-[0.35em]"
                            placeholder="000000" />
                        @error('code')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center space-x-3">
                            <button class="btn btn-outline flex-1" wire:click="resetVerification">Volver</button>
                            <button class="btn btn-primary flex-1" wire:click="confirmTwoFactor"
                                x-bind:disabled="(otp ?? '').length < 6">
                                Confirmar
                            </button>
                        </div>
                    </div>
                @else
                    @error('setupData')
                        <div class="alert alert-error border border-error/20 text-sm shadow-sm">
                            <span>{{ $message }}</span></div>
                    @enderror

                    <div class="flex justify-center">
                        <div
                            class="relative aspect-square w-64 overflow-hidden rounded-lg border border-base-content/10 bg-base-100">
                        @empty($qrCodeSvg)
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="loading loading-spinner loading-md"></span>
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full p-4">
                                <div class="bg-white p-3 rounded">{!! $qrCodeSvg !!}</div>
                            </div>
                        @endempty
                    </div>
                </div>

                <button @disabled($errors->has('setupData')) class="btn btn-primary w-full"
                    wire:click="showVerificationIfNecessary">
                    {{ $this->modalConfig['buttonText'] }}
                </button>

                <div class="space-y-3">
                    <p class="text-xs text-center text-base-content/70">o ingresa el código manualmente</p>

                    <div class="join w-full">
                        <input type="text" readonly value="{{ $manualSetupKey }}"
                            class="input input-bordered join-item w-full border-base-content/20" />
                        <button type="button" class="btn join-item" x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText('{{ $manualSetupKey }}'); copied = true; setTimeout(() => copied = false, 1500)">
                            <span x-show="!copied">Copiar</span>
                            <span x-show="copied">Copiado</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="modal-action">
            <button class="btn" wire:click="closeModal">Cerrar</button>
        </div>
    </div>
</dialog>
</section>
