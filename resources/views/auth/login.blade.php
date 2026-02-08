<x-layouts::guest :title="__('Login')">
    <div class="card w-96 bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h2 class="card-title justify-center mb-6">CODEDE - Iniciar Sesión</h2>

            <!-- Mensajes de sesión/error -->
            @if (session('status'))
                <div class="alert alert-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Email</legend>
                    <input type="email" name="email" value="{{ old('email') }}" class="input w-full" required
                        autofocus />
                    @error('email')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset mt-4">
                    <legend class="fieldset-legend">Contraseña</legend>
                    <input type="password" name="password" class="input w-full" required />
                    @error('password')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="mt-4">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary" />
                        <span class="label">Recordarme</span>
                    </label>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary w-full">Iniciar Sesión</button>
                </div>

                @if (Route::has('password.request'))
                    <div class="text-center mt-4">
                        <a href="{{ route('password.request') }}" class="link link-primary">¿Olvidaste tu
                            contraseña?</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-layouts::guest>
