<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="Confirmar contraseña"
            description="Esta es un área segura de la aplicación. Por favor confirma tu contraseña antes de continuar." />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Contraseña</legend>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    placeholder="Contraseña" class="input w-full" />
                @error('password')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <button type="submit" class="btn btn-primary w-full" data-test="confirm-password-button">
                Confirmar
            </button>
        </form>
    </div>
</x-layouts::auth>
