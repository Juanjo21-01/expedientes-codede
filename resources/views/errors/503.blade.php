<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>503 - En Mantenimiento | CODEDE San Marcos</title>
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

            {{-- Ilustración SVG - Herramientas / mantenimiento --}}
            <div class="flex justify-center">
                <div class="relative">
                    <x-heroicon-o-wrench-screwdriver class="w-44 h-44 text-secondary/20" stroke-width="0.5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-secondary/10 rounded-full p-4">
                            <x-heroicon-o-cog-6-tooth class="w-12 h-12 text-secondary" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Código de error --}}
            <h1 class="text-9xl font-black text-secondary/80 leading-none tracking-tighter">503</h1>

            {{-- Card con detalles --}}
            <div class="card bg-base-100 shadow-xl border border-base-300 mt-3">
                <div class="card-body items-center text-center">
                    <h2 class="card-title text-2xl font-bold">Sistema en Mantenimiento</h2>
                    <div class="divider my-1"></div>
                    <p class="text-base-content/70">
                        Estamos realizando mejoras y actualizaciones en el sistema.
                        Volveremos a estar en línea muy pronto.
                    </p>

                    <div class="alert alert-soft mt-4">
                        <x-heroicon-o-information-circle class="w-5 h-5" />
                        <span class="text-sm">Agradecemos tu paciencia. El mantenimiento no debería tardar mucho.</span>
                    </div>

                    {{-- Progress visual --}}
                    <div class="w-full mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-base-content/50">Progreso del mantenimiento</span>
                            <span class="loading loading-dots loading-sm text-secondary"></span>
                        </div>
                        <progress class="progress progress-secondary w-full" value="70" max="100"></progress>
                    </div>

                    <div class="card-actions mt-4 flex-wrap justify-center gap-2">
                        <a href="javascript:location.reload()" class="btn btn-secondary gap-2">
                            <x-heroicon-o-arrow-path class="w-5 h-5" />
                            Verificar disponibilidad
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

    <script>
        // Auto-refresh every 60 seconds to check if maintenance is over
        setTimeout(() => location.reload(), 60000);
    </script>
</body>

</html>
