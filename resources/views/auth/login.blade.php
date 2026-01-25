
<x-layouts::guest :title="__('Login')">
    <div class="card w-96 bg-base-100 shadow-xl">
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

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered" required
                        autofocus />
                    @error('email')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Contraseña</span>
                    </label>
                    <input type="password" name="password" class="input input-bordered" required />
                    @error('password')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control mt-4">
                    <label class="cursor-pointer label justify-start">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary" />
                        <span class="label-text ml-2">Recordarme</span>
                    </label>
                </div>

                <div class="form-control mt-6">
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
