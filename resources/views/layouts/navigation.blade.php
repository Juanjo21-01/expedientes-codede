{{-- Sidebar Navigation (drawer-side) --}}
{{-- Colapsable: en desktop muestra solo iconos cuando está cerrado, texto + iconos cuando está abierto --}}
{{-- En mobile: overlay completo con hamburger menu --}}

<div class="drawer-side max-lg:z-40 is-drawer-close:overflow-visible">
    <label for="sidebar-drawer" aria-label="cerrar sidebar" class="drawer-overlay"></label>

    <aside
        class="flex min-h-full flex-col bg-base-100 border-r border-base-300 is-drawer-close:w-18 is-drawer-open:w-72">

        {{-- Sidebar Header: CODEDE branding (clickable para toggle) --}}
        <label for="sidebar-drawer"
            class="flex items-center gap-3 p-3 pl-4 border-b border-base-300 cursor-pointer hover:bg-base-200/50 transition-colors is-drawer-close:justify-center">
            <div class="avatar placeholder shrink-0">
                <div class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center">
                    <img src="{{ asset('img/logo.png') }}" alt="CODEDE Logo" class="w-6 h-6">
                </div>
            </div>
            <div class="is-drawer-close:hidden overflow-hidden whitespace-nowrap">
                <h2 class="font-bold text-lg leading-tight">CODEDE</h2>
                <p class="text-xs text-base-content/60">San Marcos</p>
            </div>
        </label>

        {{-- Navigation Menu --}}
        <ul class="menu p-2 flex-1 w-full [&_li>a]:gap-4">
            {{-- Main --}}
            <li class="menu-title is-drawer-close:hidden">
                <span>Menú Principal</span>
            </li>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    data-tip="Dashboard">
                    <x-heroicon-o-home class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('expedientes.index') }}" wire:navigate
                    class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('expedientes.*') ? 'active' : '' }}"
                    data-tip="Expedientes">
                    <x-heroicon-o-folder-open class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Expedientes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guias') }}" wire:navigate
                    class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('guias') ? 'active' : '' }}"
                    data-tip="Guías">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Guías</span>
                </a>
            </li>

            @if (auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isJefeFinanciero())
                {{-- Admin Section --}}
                <li class="menu-title mt-4 is-drawer-close:hidden">
                    <span>Administración</span>
                </li>
                {{-- Separador visual en modo colapsado --}}
                <div class="divider my-0 is-drawer-open:hidden"></div>

                @if (auth()->user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.usuarios.index') }}" wire:navigate
                            class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}"
                            data-tip="Usuarios">
                            <x-heroicon-o-users class="w-5 h-5 shrink-0" />
                            <span class="is-drawer-close:hidden">Usuarios</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('admin.municipios.index') }}" wire:navigate
                        class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('admin.municipios.*') ? 'active' : '' }}"
                        data-tip="Municipalidades">
                        <x-heroicon-o-building-library class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Municipalidades</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.guias.index') }}" wire:navigate
                        class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('admin.guias.*') ? 'active' : '' }}"
                        data-tip="Gestión Guías">
                        <x-heroicon-o-document-check class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Gestión Guías</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.notificaciones.index') }}" wire:navigate
                        class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('admin.notificaciones.*') ? 'active' : '' }}"
                        data-tip="Notificaciones">
                        <x-heroicon-o-envelope class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Notificaciones</span>
                    </a>
                </li>
                @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                    <li>
                        <a href="{{ route('admin.bitacora') }}" wire:navigate
                            class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('admin.bitacora') ? 'active' : '' }}"
                            data-tip="Bitácora">
                            <x-heroicon-o-clock class="w-5 h-5 shrink-0" />
                            <span class="is-drawer-close:hidden">Bitácora</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- Notificaciones para Técnicos (fuera de la sección admin) --}}
            @if (auth()->user()->isTecnico())
                <li class="menu-title mt-4 is-drawer-close:hidden">
                    <span>Herramientas</span>
                </li>
                <div class="divider my-0 is-drawer-open:hidden"></div>
                <li>
                    <a href="{{ route('admin.notificaciones.index') }}" wire:navigate
                        class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('admin.notificaciones.*') ? 'active' : '' }}"
                        data-tip="Notificaciones">
                        <x-heroicon-o-envelope class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Notificaciones</span>
                    </a>
                </li>
            @endif

            @if (in_array(auth()->user()->role->nombre, ['Administrador', 'Director General', 'Jefe Administrativo-Financiero', 'Técnico']))
                {{-- Reports Section --}}
                <li class="menu-title mt-4 is-drawer-close:hidden">
                    <span>Reportes</span>
                </li>
                <div class="divider my-0 is-drawer-open:hidden"></div>
                <li>
                    <a href="{{ route('reportes') }}" wire:navigate
                        class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center {{ request()->routeIs('reportes') ? 'active' : '' }}"
                        data-tip="Ver Reportes">
                        <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Ver Reportes</span>
                    </a>
                </li>
            @endif
        </ul>

        {{-- Toggle expand/collapse (solo desktop) --}}
        <div class="hidden lg:block border-t border-base-300 p-2">
            <div class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center"
                data-tip="Expandir menú">
                <label for="sidebar-drawer" class="btn btn-ghost btn-sm w-full justify-center gap-3">
                    <x-heroicon-o-chevron-double-right
                        class="w-4 h-4 shrink-0 is-drawer-open:rotate-180 transition-transform duration-300" />
                    <span class="is-drawer-close:hidden">Colapsar</span>
                </label>
            </div>
        </div>

        {{-- Sidebar Footer --}}
        <div class="border-t border-base-300 p-2">

            {{-- User Info --}}
            <div class="flex items-center gap-3 px-2 pb-1 is-drawer-close:justify-center is-drawer-close:p-2">
                <div class="avatar placeholder shrink-0">
                    <div class="bg-neutral text-neutral-content rounded-full w-8 h-8 flex items-center justify-center">
                        <span class="text-xs font-semibold">{{ auth()->user()->iniciales ?? 'U' }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0 is-drawer-close:hidden p-1">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->nombres ?? 'Usuario' }}</p>
                    <p class="text-xs text-base-content/60 truncate">
                        {{ auth()->user()->role->nombre ?? 'Sin rol' }}</p>
                </div>
            </div>
        </div>
    </aside>
</div>
