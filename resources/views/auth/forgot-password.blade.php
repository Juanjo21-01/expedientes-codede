<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="¿Olvidaste tu contraseña?"
            description="Ingresa tu correo electrónico para recibir un enlace de restablecimiento" />

        <!-- Estado de sesión -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Correo electrónico</legend>
                <input id="email" name="email" type="email" required autofocus placeholder="correo@ejemplo.com"
                    class="input w-full" />
                @error('email')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <button type="submit" class="btn btn-primary w-full" data-test="email-password-reset-link-button">
                Enviar enlace de restablecimiento
            </button>
        </form>

        <div class="text-center text-sm text-base-content/70">
            <span>O volver a</span>
            <a href="{{ route('login') }}" wire:navigate class="link link-primary ml-1">iniciar sesión</a>
        </div>
    </div>
</x-layouts::auth>
