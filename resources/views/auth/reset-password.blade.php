<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="Restablecer contraseña" description="Ingresa tu nueva contraseña" />

        <!-- Estado de sesión -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}"
            class="card border border-base-content/10 bg-base-100/60 shadow-sm">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="card-body gap-5 p-5">

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Correo electrónico</legend>
                    <input id="email" name="email" value="{{ request('email') }}" type="email" required
                        autocomplete="email" class="input input-bordered w-full border-base-content/20" />
                    @error('email')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nueva contraseña</legend>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        placeholder="Nueva contraseña" class="input input-bordered w-full border-base-content/20" />
                    @error('password')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Confirmar contraseña</legend>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        autocomplete="new-password" placeholder="Confirmar contraseña"
                        class="input input-bordered w-full border-base-content/20" />
                </fieldset>

                <button type="submit" class="btn btn-primary w-full" data-test="reset-password-button">
                    Restablecer contraseña
                </button>
            </div>
        </form>
    </div>
</x-layouts::auth>
