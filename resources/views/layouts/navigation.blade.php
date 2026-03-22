{{-- Sidebar Navigation (drawer-side) --}}
{{-- Colapsable: en desktop muestra solo iconos cuando está cerrado, texto + iconos cuando está abierto --}}
{{-- En mobile: overlay completo con hamburger menu --}}
@php
    $collapsedItemClasses =
        'is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center';
    $menuItemBase =
        'group flex items-center rounded-xl px-2.5 py-1.5 text-[13px] font-medium transition-colors xl:px-3 xl:py-2 xl:text-sm';
    $menuItemActive = 'border border-primary/20 bg-primary/10 text-primary';
    $menuItemInactive = 'text-base-content/75 hover:bg-base-200/70 hover:text-base-content';
    $sectionTitleClasses =
        'menu-title mt-1.5 px-2.5 py-0 text-[10px] font-semibold uppercase tracking-wide text-base-content/45 xl:mt-2 xl:pb-1 xl:text-[11px] is-drawer-close:hidden';
@endphp

<div class="drawer-side max-lg:z-40 is-drawer-close:overflow-visible">
    <label for="sidebar-drawer" aria-label="cerrar sidebar" class="drawer-overlay lg:hidden"></label>

    <aside
        class="flex min-h-full flex-col border-r border-base-content/10 bg-base-100 shadow-xl transition-[width] duration-300 lg:shadow-none is-drawer-close:w-18 is-drawer-open:w-64 xl:is-drawer-open:w-68">

        {{-- Sidebar Header: CODEDE branding (clickable para toggle) --}}
        <label for="sidebar-drawer"
            class="flex cursor-pointer items-center gap-3 border-b border-base-content/10 p-2.5 pl-3 transition-colors hover:bg-base-200/50 xl:p-3 xl:pl-4 is-drawer-close:justify-center">
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
        <ul
            class="menu flex-1 w-full gap-1 p-2 [&_a_svg]:h-4 [&_a_svg]:w-4 xl:[&_a_svg]:h-5 xl:[&_a_svg]:w-5 is-drawer-open:overflow-hidden is-drawer-close:overflow-visible">
            {{-- Main --}}
            <li
                class="menu-title px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-base-content/45 is-drawer-close:hidden">
                <span>Menú Principal</span>
            </li>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('dashboard') ? $menuItemActive : $menuItemInactive }}"
                    data-tip="Dashboard">
                    <x-heroicon-o-home class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('expedientes.index') }}" wire:navigate
                    class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('expedientes.*') ? $menuItemActive : $menuItemInactive }}"
                    data-tip="Expedientes">
                    <x-heroicon-o-folder-open class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Expedientes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guias') }}" wire:navigate
                    class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('guias') ? $menuItemActive : $menuItemInactive }}"
                    data-tip="Guías">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Guías</span>
                </a>
            </li>
            <li>
                <a href="{{ route('municipios.index') }}" wire:navigate
                    class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('municipios.*') ? $menuItemActive : $menuItemInactive }}"
                    data-tip="Municipalidades">
                    <x-heroicon-o-building-library class="w-5 h-5 shrink-0" />
                    <span class="is-drawer-close:hidden">Municipalidades</span>
                </a>
            </li>
            @if (auth()->user()->isMunicipal())
                <li>
                    <a href="{{ route('notificaciones.index') }}" wire:navigate
                        class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('notificaciones.*') ? $menuItemActive : $menuItemInactive }}"
                        data-tip="Mis Notificaciones">
                        <x-heroicon-o-envelope-open class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Mis Notificaciones</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isJefeFinanciero())
                {{-- Admin Section --}}
                <li class="{{ $sectionTitleClasses }}">
                    <span>Administración</span>
                </li>
                {{-- Separador visual en modo colapsado --}}
                <li class="divider my-0 is-drawer-open:hidden" aria-hidden="true"></li>

                @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                    <li>
                        <a href="{{ route('admin.usuarios.index') }}" wire:navigate
                            class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('admin.usuarios.*') ? $menuItemActive : $menuItemInactive }}"
                            data-tip="Usuarios">
                            <x-heroicon-o-users class="w-5 h-5 shrink-0" />
                            <span class="is-drawer-close:hidden">Usuarios</span>
                        </a>
                    </li>
                @endif
                @if (auth()->user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.municipios.index') }}" wire:navigate
                            class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('admin.municipios.*') ? $menuItemActive : $menuItemInactive }}"
                            data-tip="Gestión Municipios">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0" />
                            <span class="is-drawer-close:hidden">Gestión Municipios</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('admin.guias.index') }}" wire:navigate
                        class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('admin.guias.*') ? $menuItemActive : $menuItemInactive }}"
                        data-tip="Gestión Guías">
                        <x-heroicon-o-document-check class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Gestión Guías</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.notificaciones.index') }}" wire:navigate
                        class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('admin.notificaciones.*') ? $menuItemActive : $menuItemInactive }}"
                        data-tip="Notificaciones">
                        <x-heroicon-o-envelope class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Notificaciones</span>
                    </a>
                </li>
                @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                    <li>
                        <a href="{{ route('admin.bitacora') }}" wire:navigate
                            class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('admin.bitacora') ? $menuItemActive : $menuItemInactive }}"
                            data-tip="Bitácora">
                            <x-heroicon-o-clock class="w-5 h-5 shrink-0" />
                            <span class="is-drawer-close:hidden">Bitácora</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- Herramientas para Técnicos --}}
            @if (auth()->user()->isTecnico())
                <li class="{{ $sectionTitleClasses }}">
                    <span>Herramientas</span>
                </li>
                <li class="divider my-0 is-drawer-open:hidden" aria-hidden="true"></li>
                <li>
                    <a href="{{ route('notificaciones.index') }}" wire:navigate
                        class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('notificaciones.*') ? $menuItemActive : $menuItemInactive }}"
                        data-tip="Notificaciones">
                        <x-heroicon-o-envelope class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Mis Notificaciones</span>
                    </a>
                </li>
            @endif

            @if (in_array(auth()->user()->role->nombre, [
                    'Administrador',
                    'Director General',
                    'Jefe Administrativo-Financiero',
                    'Técnico',
                ]))
                {{-- Reports Section --}}
                <li class="{{ $sectionTitleClasses }}">
                    <span>Reportes</span>
                </li>
                <li class="divider my-0 is-drawer-open:hidden" aria-hidden="true"></li>
                <li>
                    <a href="{{ route('reportes') }}" wire:navigate
                        class="{{ $collapsedItemClasses }} {{ $menuItemBase }} {{ request()->routeIs('reportes') ? $menuItemActive : $menuItemInactive }}"
                        data-tip="Ver Reportes">
                        <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0" />
                        <span class="is-drawer-close:hidden">Ver Reportes</span>
                    </a>
                </li>
            @endif
        </ul>

        {{-- Toggle expand/collapse (solo desktop) --}}
        <div class="hidden lg:block border-t border-base-content/10 p-1.5">
            <div class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:justify-center"
                data-tip="Expandir menú">
                <label for="sidebar-drawer"
                    class="btn btn-ghost btn-sm w-full justify-center gap-3 rounded-xl hover:bg-base-200/80">
                    <x-heroicon-o-chevron-double-right
                        class="w-4 h-4 shrink-0 is-drawer-open:rotate-180 transition-transform duration-300" />
                    <span class="is-drawer-close:hidden">Colapsar</span>
                </label>
            </div>
        </div>

        {{-- Sidebar Footer --}}
        <div class="border-t border-base-content/10 p-2">

            {{-- User Info --}}
            <div
                class="rounded-xl bg-base-200/60 px-1.5 py-0.5 transition-colors hover:bg-base-200/80 xl:px-2 is-drawer-close:bg-transparent is-drawer-close:hover:bg-transparent is-drawer-close:p-2">
                <div
                    class="flex items-center gap-2.5 px-1.5 xl:gap-3 xl:px-2 is-drawer-close:justify-center is-drawer-close:px-0">
                    <div class="avatar placeholder shrink-0">
                        <div
                            class="bg-neutral text-neutral-content rounded-full w-8 h-8 flex items-center justify-center">
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
        </div>
    </aside>
</div>
