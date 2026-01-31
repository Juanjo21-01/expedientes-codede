<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" id="html">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CODEDE {{ $title ?? '' }}</title>

    {{-- Icon --}}
    <link rel="icon" href="{{ asset('img/icono.png') }}" type="image/png">

    <!-- Fonts -->

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        // Dark mode simple (localStorage)
        document.addEventListener('DOMContentLoaded', () => {
            const html = document.getElementById('html');
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                    '(prefers-color-scheme: dark)').matches)) {
                html.setAttribute('data-theme', 'dark');
            } else {
                html.setAttribute('data-theme', 'light');
            }
        });

        function toggleTheme() {
            const html = document.getElementById('html');
            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light');
                localStorage.theme = 'light';
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</head>

<body class="g-base-200 min-h-screen">

    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow">
        <div class="flex-none lg:hidden">
            <label for="sidebar-drawer" class="btn btn-square btn-ghost drawer-button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="inline-block w-6 h-6 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </label>
        </div>
        <div class="flex-1">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xl">CODEDE San Marcos</a>
        </div>

        <div class="flex-none gap-4">
            <!-- Dark mode toggle -->
            <button onclick="toggleTheme()" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                    id="Layer_1" x="0px" y="0px" viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;"
                    xml:space="preserve" width="80" height="80" class="h-8 w-8">
                    <g>
                        <path style="fill:#8BB7F0;"
                            d="M22,61.5C10.145,61.5,0.5,51.855,0.5,40S10.145,18.5,22,18.5h36c11.855,0,21.5,9.645,21.5,21.5   S69.855,61.5,58,61.5H22z" />
                        <g>
                            <path style="fill:#4E7AB5;"
                                d="M58,19c11.579,0,21,9.421,21,21c0,11.579-9.421,21-21,21H22C10.421,61,1,51.579,1,40    c0-11.579,9.421-21,21-21H58 M58,18H22C9.85,18,0,27.85,0,40v0c0,12.15,9.85,22,22,22h36c12.15,0,22-9.85,22-22v0    C80,27.85,70.15,18,58,18L58,18z" />
                        </g>
                    </g>
                    <path style="fill:#FFFFFF;"
                        d="M58,56L58,56c8.837,0,16-7.163,16-16v0c0-8.837-7.163-16-16-16h0c-8.837,0-16,7.163-16,16v0  C42,48.837,49.163,56,58,56z" />
                </svg>

            </button>
            <!-- User dropdown -->
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost avatar">
                    <div class="w-10 rounded-full">
                        <div class="bg-neutral text-neutral-content w-10 rounded-full flex items-center justify-center">
                            <span class="text-xl">{{ auth()->user()->nombres[0] ?? 'U' }}</span>
                        </div>
                    </div>
                </label>
                <ul tabindex="0" class="menu dropdown-content bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                    <li><a>Perfil (próximamente)</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Drawer Sidebar (DaisyUI puro, toggle con checkbox) -->
    <div class="drawer drawer-mobile">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex ">
            <aside>
                <label for="sidebar-drawer" class="drawer-overlay"></label>
                <ul class="menu p-4 w-80 bg-base-300 text-base-content h-full">
                    <li class="menu-title"><span>Menú Principal</span></li>
                    <li><a href="{{ route('dashboard') }}"
                            class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                    <li><a href="#"
                            class="{{ request()->routeIs('expedientes.*') ? 'active' : '' }}">Expedientes</a>
                    </li>
                    <li><a href="#">Guía /
                            Checklist</a></li>

                    @if (auth()->user()->role->nombre === 'Administrador')
                        <li class="menu-title mt-4"><span>Administración</span></li>
                        <li><a href="{{ route('admin.usuarios.index') }}"
                                class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">Usuarios</a>
                        </li>
                        <li><a href="#"
                                class="{{ request()->routeIs('admin.municipios.*') ? 'active' : '' }}">Municipalidades</a>
                        </li>
                        <li><a href="#" class="{{ request()->routeIs('admin.guias.*') ? 'active' : '' }}">Gestión
                                Guías</a></li>
                        <li><a href="#" class="{{ request()->routeIs('bitacora') ? 'active' : '' }}">Bitácora</a>
                        </li>
                    @endif

                    @if (in_array(auth()->user()->role->nombre, ['Administrador', 'Director', 'Jefe Administrativo-Financiero']))
                        <li><a href="#" class="{{ request()->routeIs('reportes') ? 'active' : '' }}">Reportes</a>
                        </li>
                    @endif
                </ul>
            </aside>

            <main class="h-full overflow-y-auto grow">
                <div class="container px-6 mx-auto py-3">
                    {{ $slot }}
                </div>
            </main>
        </div>
        <div class="drawer-side">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            <ul class="menu p-4 w-80 bg-base-100 text-base-content h-full">
                <li class="menu-title"><span>Menú Principal</span></li>
                <li><a href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="#" class="{{ request()->routeIs('expedientes.*') ? 'active' : '' }}">Expedientes</a>
                </li>
                <li><a href="#">Guía /
                        Checklist</a></li>

                @if (auth()->user()->role->nombre === 'Administrador')
                    <li class="menu-title mt-4"><span>Administración</span></li>
                    <li><a href="#"
                            class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">Usuarios</a></li>
                    <li><a href="#"
                            class="{{ request()->routeIs('admin.municipios.*') ? 'active' : '' }}">Municipalidades</a>
                    </li>
                    <li><a href="#" class="{{ request()->routeIs('admin.guias.*') ? 'active' : '' }}">Gestión
                            Guías</a></li>
                    <li><a href="#" class="{{ request()->routeIs('bitacora') ? 'active' : '' }}">Bitácora</a>
                    </li>
                @endif

                @if (in_array(auth()->user()->role->nombre, ['Administrador', 'Director', 'Jefe Administrativo-Financiero']))
                    <li><a href="#" class="{{ request()->routeIs('reportes') ? 'active' : '' }}">Reportes</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    @livewireScripts
</body>

</html>
