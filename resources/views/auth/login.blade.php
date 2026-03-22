<x-layouts::auth :title="'- Iniciar Sesión'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Iniciar Sesión" description="Ingresa tus credenciales para acceder al sistema" />

        <!-- Mensajes de sesión -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}"
            class="card border border-base-content/10 bg-base-100/60 shadow-sm">
            @csrf

            <div class="card-body gap-5 p-5">

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Correo electrónico</legend>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input input-bordered w-full border-base-content/20" required autofocus
                        autocomplete="email" placeholder="correo@ejemplo.com" />
                    @error('email')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Contraseña</legend>
                    <input type="password" name="password" class="input input-bordered w-full border-base-content/20"
                        required autocomplete="current-password" />
                    @error('password')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="flex items-center justify-between">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                        <span class="text-sm">Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link link-primary text-sm">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary w-full">Iniciar Sesión</button>
            </div>
        </form>
    </div>
</x-layouts::auth>
