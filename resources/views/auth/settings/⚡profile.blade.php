<?php

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $nombres = '';
    public string $apellidos = '';
    public string $cargo = '';
    public string $telefono = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->nombres = Auth::user()->nombres;
        $this->apellidos = Auth::user()->apellidos;
        $this->cargo = Auth::user()->cargo ?? '';
        $this->telefono = Auth::user()->telefono ?? '';
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'nombres' => ['required', 'string', 'max:50'],
            'apellidos' => ['required', 'string', 'max:50'],
            'cargo' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:8'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->nombre_completo);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && !Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return !(Auth::user() instanceof MustVerifyEmail) || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    <h2 class="sr-only">Configuración de perfil</h2>

    <x-auth.settings.layout heading="Perfil" subheading="Actualiza tu información de perfil">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nombres <span class="text-error">*</span></legend>
                <input id="nombres" wire:model="nombres" type="text" required autofocus autocomplete="given-name"
                    class="input w-full" />
                @error('nombres')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Apellidos <span class="text-error">*</span></legend>
                <input id="apellidos" wire:model="apellidos" type="text" required autocomplete="family-name"
                    class="input w-full" />
                @error('apellidos')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Cargo</legend>
                <input id="cargo" wire:model="cargo" type="text" autocomplete="organization-title"
                    class="input w-full" />
                @error('cargo')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Teléfono</legend>
                <input id="telefono" wire:model="telefono" type="text" autocomplete="tel"
                    class="input w-full" />
                @error('telefono')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Correo electrónico <span class="text-error">*</span></legend>
                <input id="email" wire:model="email" type="email" required autocomplete="email"
                    class="input w-full" />
                @error('email')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <p class="mt-4 text-sm text-base-content/70">
                            Tu dirección de correo no está verificada.

                            <button type="button" class="link link-primary text-sm"
                                wire:click.prevent="resendVerificationNotification">
                                Haz clic aquí para reenviar el correo de verificación.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-success">
                                Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                            </p>
                        @endif
                    </div>
                @endif
            </fieldset>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-primary w-full" data-test="update-profile-button">
                        Guardar
                    </button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    Guardado.
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            @livewire('auth::settings.delete-user-form')
        @endif
    </x-auth.settings.layout>
</section>
