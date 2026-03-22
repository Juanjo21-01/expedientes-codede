<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>404 - Página No Encontrada | CODEDE San Marcos</title>
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
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-xl text-center">

            {{-- Ilustración SVG - Brújula / mapa perdido --}}
            <div class="flex justify-center">
                <div class="relative">
                    <x-heroicon-o-map class="w-44 h-44 text-warning/20" stroke-width="0.5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-warning/10 rounded-full p-4">
                            <x-heroicon-o-magnifying-glass class="w-12 h-12 text-warning" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Código de error --}}
            <h1 class="text-9xl font-black leading-none tracking-tighter text-warning/80">404</h1>

            {{-- Card con detalles --}}
            <div class="card mt-3 border border-base-content/10 bg-base-100 shadow-lg">
                <div class="card-body items-center text-center">
                    <span class="badge badge-warning badge-soft badge-sm">HTTP 404</span>
                    <h2 class="card-title text-2xl font-bold">Página No Encontrada</h2>
                    <div class="divider my-1"></div>
                    <p class="text-base-content/70">
                        La página que buscas no existe o fue movida a otra ubicación.
                        Verifica que la dirección URL sea correcta.
                    </p>

                    <div class="alert alert-warning alert-soft mt-4">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        <span class="text-sm">Es posible que el enlace que seguiste esté desactualizado o roto.</span>
                    </div>

                    <div class="card-actions mt-4 flex-wrap justify-center gap-2">
                        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
                            class="btn btn-ghost gap-2">
                            <x-heroicon-o-arrow-uturn-left class="w-5 h-5" />
                            Volver atrás
                        </a>
                        <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="btn btn-warning gap-2">
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
