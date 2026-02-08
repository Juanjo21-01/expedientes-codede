<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CODEDE {{ $title ?? '' }}</title>

    {{-- Icon --}}
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">

    {{-- Aplicar tema ANTES de cualquier renderizado para evitar flash --}}
    <script>
        (function() {
            const theme = localStorage.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        // Función para sincronizar todos los toggles de tema con el estado actual
        function syncThemeToggles() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            document.querySelectorAll('.theme-toggle-checkbox').forEach(function(toggle) {
                toggle.checked = isDark;
            });
        }

        // Función para cambiar el tema
        function toggleTheme() {
            const html = document.documentElement;
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.theme = newTheme;
            syncThemeToggles();
        }

        // Sincronizar toggles en carga inicial
        document.addEventListener('DOMContentLoaded', function() {
            syncThemeToggles();
        });

        // Re-aplicar tema y sincronizar toggles después de cada navegación con wire:navigate
        document.addEventListener('livewire:navigated', function() {
            const theme = localStorage.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ?
                'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
            syncThemeToggles();
            initSidebarState();
        });

        // Sidebar: recordar estado expandido/colapsado en desktop
        function initSidebarState() {
            if (window.innerWidth >= 1024) {
                const toggle = document.getElementById('sidebar-drawer');
                if (toggle && localStorage.getItem('sidebar-open') === 'true') {
                    toggle.checked = true;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initSidebarState();
            const toggle = document.getElementById('sidebar-drawer');
            if (toggle) {
                toggle.addEventListener('change', function() {
                    if (window.innerWidth >= 1024) {
                        localStorage.setItem('sidebar-open', this.checked);
                    }
                });
            }
        });
    </script>
</head>

<body class="bg-base-200 min-h-screen">
    <!-- Drawer Layout -->
    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            <nav class="navbar bg-base-100 shadow-md sticky top-0 z-30">
                <!-- Mobile menu button -->
                <div class="flex-none lg:hidden">
                    <label for="sidebar-drawer" class="btn btn-square btn-ghost drawer-button">
                        <x-heroicon-o-bars-3 class="w-6 h-6" />
                    </label>
                </div>

                {{-- Spacer / Breadcrumbs area --}}
                <div class="flex-1 px-2"></div>

                <div class="flex-none gap-2">
                    <!-- Dark mode toggle -->
                    <label class="swap swap-rotate btn btn-ghost btn-circle">
                        <input type="checkbox" class="theme-toggle-checkbox" onclick="toggleTheme()" />
                        <!-- Sun icon -->
                        <svg class="swap-off fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24">
                            <path
                                d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
                        </svg>
                        <!-- Moon icon -->
                        <svg class="swap-on fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24">
                            <path
                                d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
                        </svg>
                    </label>

                    <!-- User dropdown -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                            <div
                                class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center">
                                <span class="text-lg font-semibold">{{ auth()->user()->iniciales ?? 'U' }}</span>
                            </div>
                        </label>
                        <ul tabindex="0"
                            class="menu dropdown-content bg-base-100 rounded-box z-50 mt-3 w-56 p-2 shadow-lg border border-base-300">
                            <li class="menu-title">
                                <span class="text-xs">{{ auth()->user()->nombres ?? 'Usuario' }}</span>
                            </li>
                            <li>
                                <a class="flex items-center gap-2">
                                    <x-heroicon-o-user class="w-5 h-5" />
                                    Perfil
                                    <span class="badge badge-sm badge-ghost">Próximamente</span>
                                </a>
                            </li>
                            <div class="divider my-1"></div>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-2 w-full p-0">
                                    @csrf
                                    <button type="submit" class="text-error flex items-center gap-2 w-full cursor-pointer p-2">
                                        <x-heroicon-o-arrow-left-start-on-rectangle class="w-5 h-5" />
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            {{-- Toast Notifications --}}
            <x-toast-notification />

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </div>
            </main>

            {{-- Footer --}}
            @include('layouts.footer')
        </div>

        {{-- Sidebar Navigation --}}
        @include('layouts.navigation')
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @livewireScripts
</body>

</html>
