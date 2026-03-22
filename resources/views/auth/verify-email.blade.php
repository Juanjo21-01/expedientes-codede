<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="Verificar correo electrónico"
            description="Por favor verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar." />

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success text-sm">
                <span>Se ha enviado un nuevo enlace de verificación al correo electrónico que proporcionaste durante el
                    registro.</span>
            </div>
        @endif

        <div class="card border border-base-content/10 bg-base-100/60 shadow-sm">
            <div class="card-body gap-4 p-5">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">
                        Reenviar correo de verificación
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm w-full" data-test="logout-button">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::auth>
