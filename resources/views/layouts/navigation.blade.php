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
            <svg id="light-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg id="dark-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
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
