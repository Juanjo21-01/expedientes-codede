<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>419 - Página Expirada | CODEDE San Marcos</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">

    <script>
        (function() {
            const theme = localStorage.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-base-200">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-lg w-full text-center">

            {{-- Ilustración SVG - Reloj / tiempo expirado --}}
            <div class="flex justify-center">
                <div class="relative">
                    <x-heroicon-o-clock class="w-44 h-44 text-info/20" stroke-width="0.5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-info/10 rounded-full p-4">
                            <x-heroicon-o-clock class="w-12 h-12 text-info" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Código de error --}}
            <h1 class="text-9xl font-black text-info/80 leading-none tracking-tighter">419</h1>

            {{-- Card con detalles --}}
            <div class="card bg-base-100 shadow-xl border border-base-300 mt-3">
                <div class="card-body items-center text-center">
                    <h2 class="card-title text-2xl font-bold">Página Expirada</h2>
                    <div class="divider my-1"></div>
                    <p class="text-base-content/70">
                        Tu sesión o token de seguridad ha expirado.
                        Esto suele ocurrir cuando permaneces inactivo por mucho tiempo.
                    </p>

                    <div class="alert alert-info alert-soft mt-4">
                        <x-heroicon-o-information-circle class="w-5 h-5" />
                        <span class="text-sm">Recarga la página o inicia sesión nuevamente para continuar.</span>
                    </div>

                    <div class="card-actions mt-4 flex-wrap justify-center gap-2">
                        <a href="javascript:location.reload()" class="btn btn-ghost gap-2">
                            <x-heroicon-o-arrow-path class="w-5 h-5" />
                            Recargar página
                        </a>
                        <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="btn btn-info gap-2">
                            <x-heroicon-o-home class="w-5 h-5" />
                            Ir al Inicio
                        </a>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <p class="text-xs text-base-content/40 mt-8">
                © {{ date('Y') }} CODEDE San Marcos — Sistema de Gestión de Expedientes
            </p>
        </div>
    </div>
</body>

</html>
