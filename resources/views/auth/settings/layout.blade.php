<div class="mx-auto grid w-full max-w-6xl grid-cols-1 gap-5 md:grid-cols-[15rem_minmax(0,1fr)] md:gap-6">
    <aside class="w-full">
        <div class="card border border-base-content/10 bg-base-100 shadow-sm md:sticky md:top-6">
            <div class="card-body p-3">
                <p class="px-2 pb-1 text-xs font-semibold uppercase tracking-wide text-base-content/55">Configuración</p>
                <ul class="menu menu-sm gap-1" aria-label="Configuración">
                    <li>
                        <a href="{{ route('profile.edit') }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition-colors {{ request()->routeIs('profile.edit') ? 'border border-primary/20 bg-primary/10 font-semibold text-primary' : 'text-base-content/80 hover:bg-base-200/70 hover:text-base-content' }}">
                            <x-heroicon-o-user class="h-4 w-4 shrink-0" />
                            <span>Perfil</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user-password.edit') }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition-colors {{ request()->routeIs('user-password.edit') ? 'border border-primary/20 bg-primary/10 font-semibold text-primary' : 'text-base-content/80 hover:bg-base-200/70 hover:text-base-content' }}">
                            <x-heroicon-o-key class="h-4 w-4 shrink-0" />
                            <span>Contraseña</span>
                        </a>
                    </li>
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <li>
                            <a href="{{ route('two-factor.show') }}" wire:navigate
                                class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition-colors {{ request()->routeIs('two-factor.show') ? 'border border-primary/20 bg-primary/10 font-semibold text-primary' : 'text-base-content/80 hover:bg-base-200/70 hover:text-base-content' }}">
                                <x-heroicon-o-shield-check class="h-4 w-4 shrink-0" />
                                <span>Autenticación 2FA</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('appearance.edit') }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition-colors {{ request()->routeIs('appearance.edit') ? 'border border-primary/20 bg-primary/10 font-semibold text-primary' : 'text-base-content/80 hover:bg-base-200/70 hover:text-base-content' }}">
                            <x-heroicon-o-cog-6-tooth class="h-4 w-4 shrink-0" />
                            <span>Apariencia</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

    <div class="w-full">
        <div class="card w-full max-w-4xl border border-base-content/10 bg-base-100 shadow-sm">
            <div class="card-body p-5 sm:p-6">
                <h1 class="text-xl font-semibold tracking-tight text-base-content">{{ $heading ?? '' }}</h1>
                <p class="mt-1 text-sm text-base-content/70">{{ $subheading ?? '' }}</p>

                <div class="mt-5 w-full max-w-3xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
