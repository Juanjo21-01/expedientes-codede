<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>500 - Error del Servidor | CODEDE San Marcos</title>
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

            {{-- Ilustración SVG - Servidor / engranaje roto --}}
            <div class="flex justify-center mb-0">
                <div class="relative">
                    <x-heroicon-o-server-stack class="w-44 h-44 text-error/20" stroke-width="0.5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-error/10 rounded-full p-4">
                            <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-error" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Código de error --}}
            <h1 class="text-9xl font-black text-error/80 leading-none tracking-tighter mt-0">500</h1>

            {{-- Card con detalles --}}
            <div class="card bg-base-100 shadow-xl border border-base-300 mt-2">
                <div class="card-body items-center text-center">
                    <h2 class="card-title text-2xl font-bold">Error del Servidor</h2>
                    <div class="divider my-1"></div>
                    <p class="text-base-content/70">
                        Ocurrió un problema interno en el servidor.
                        Nuestro equipo técnico ha sido notificado del incidente.
                    </p>

                    <div class="alert alert-error alert-soft mt-4">
                        <x-heroicon-o-wrench-screwdriver class="w-5 h-5" />
                        <span class="text-sm">Si el problema persiste, intenta de nuevo en unos minutos o contacta al
                            administrador.</span>
                    </div>

                    <div class="card-actions mt-4 flex-wrap justify-center gap-2">
                        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
                            class="btn btn-ghost gap-2">
                            <x-heroicon-o-arrow-uturn-left class="w-5 h-5" />
                            Volver atrás
                        </a>
                        <a href="/" class="btn btn-error gap-2">
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
