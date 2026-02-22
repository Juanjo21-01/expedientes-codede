<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="Restablecer contraseña" description="Ingresa tu nueva contraseña" />

        <!-- Estado de sesión -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Correo electrónico</legend>
                <input id="email" name="email" value="{{ request('email') }}" type="email" required
                    autocomplete="email" class="input w-full" />
                @error('email')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nueva contraseña</legend>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    placeholder="Nueva contraseña" class="input w-full" />
                @error('password')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Confirmar contraseña</legend>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    autocomplete="new-password" placeholder="Confirmar contraseña" class="input w-full" />
            </fieldset>

            <button type="submit" class="btn btn-primary w-full" data-test="reset-password-button">
                Restablecer contraseña
            </button>
        </form>
    </div>
</x-layouts::auth>
