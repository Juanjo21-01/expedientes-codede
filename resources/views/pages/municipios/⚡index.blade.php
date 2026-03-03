<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Municipio;
use App\Models\Expediente;
use App\Models\Role;

new #[Title('- Municipalidades')] class extends Component {
    public string $search = '';

    public function mount(): void
    {
        $user = auth()->user();

        // Municipal con 1 solo municipio → redirigir directo al show
        if ($user->isMunicipal()) {
            $municipioIds = $user->municipios_ids;
            if (count($municipioIds) === 1) {
                $this->redirectRoute('municipios.show', $municipioIds[0], navigate: true);
                return;
            }
        }
    }

    #[Computed]
    public function municipios()
    {
        $user = auth()->user();

        return Municipio::query()
            ->when(!$user->hasGlobalAccess(), fn($q) => $q->whereIn('id', $user->municipios_ids))
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->ordenados()
            ->withCount(['expedientes', 'expedientes as expedientes_activos_count' => fn($q) => $q->activos(), 'expedientes as expedientes_aprobados_count' => fn($q) => $q->aprobados()])
            ->with([
                'users' => fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('nombre', [Role::MUNICIPAL, Role::TECNICO]))->where('users.estado', true)->with('role:id,nombre'),
            ])
            ->get();
    }

    #[Computed]
    public function totalMunicipios(): int
    {
        $user = auth()->user();

        return Municipio::query()
            ->when(!$user->hasGlobalAccess(), fn($q) => $q->whereIn('id', $user->municipios_ids))
            ->count();
    }

    public function updatedSearch(): void
    {
        unset($this->municipios);
    }
};
?>

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 text-primary rounded-btn p-2.5">
                <x-heroicon-o-building-library class="w-6 h-6" />
            </div>
            <div>
                <h1 class="text-2xl font-bold">Municipalidades</h1>
                <p class="text-base-content/60 text-sm">
                    @if (auth()->user()->hasGlobalAccess())
                        Vista general de los municipios de San Marcos
                    @else
                        Municipios asignados a tu usuario
                    @endif
                </p>
            </div>
        </div>

        <div class="stats bg-base-100 shadow-sm border border-base-300">
            <div class="stat py-2 px-5">
                <div class="stat-figure text-primary">
                    <x-heroicon-o-map class="w-6 h-6" />
                </div>
                <div class="stat-title text-xs">
                    {{ auth()->user()->hasGlobalAccess() ? 'Total Municipios' : 'Asignados' }}
                </div>
                <div class="stat-value text-xl text-primary">{{ $this->totalMunicipios }}</div>
            </div>
        </div>
    </div>

    {{-- Búsqueda --}}
    @if ($this->totalMunicipios > 1)
        <div class="mb-6">
            <label class="input flex items-center gap-2 max-w-md">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar municipio por nombre..." class="grow" />
                @if ($search)
                    <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </label>
        </div>
    @endif

    {{-- Grid de Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse ($this->municipios as $municipio)
            <a href="{{ route('municipios.show', $municipio->id) }}" wire:navigate wire:key="mun-{{ $municipio->id }}"
                class="card bg-base-100 shadow-sm border border-base-300 hover:shadow-lg hover:border-primary/40 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group">
                <div class="card-body p-4 gap-3">
                    {{-- Header: Nombre + Estado --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex items-center gap-2">
                            <div class="avatar placeholder shrink-0">
                                <div class="bg-primary/10 text-primary rounded-lg w-9 h-9 flex items-center justify-center">
                                    <x-heroicon-o-building-library class="w-4 h-4" />
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm group-hover:text-primary transition-colors truncate">
                                    {{ $municipio->nombre }}
                                </h3>
                                <p class="text-xs text-base-content/50">{{ $municipio->departamento }}</p>
                            </div>
                        </div>
                        <span
                            class="badge badge-xs shrink-0 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                            {{ $municipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    {{-- Estadísticas de expedientes --}}
                    <div class="grid grid-cols-3 gap-1.5">
                        <div class="bg-primary/5 rounded-lg py-2 px-1 text-center">
                            <div class="text-lg font-bold text-primary leading-none">{{ $municipio->expedientes_count }}
                            </div>
                            <div class="text-[10px] text-base-content/50 uppercase mt-1">Total</div>
                        </div>
                        <div class="bg-info/5 rounded-lg py-2 px-1 text-center">
                            <div class="text-lg font-bold text-info leading-none">
                                {{ $municipio->expedientes_activos_count }}</div>
                            <div class="text-[10px] text-base-content/50 uppercase mt-1">Activos</div>
                        </div>
                        <div class="bg-success/5 rounded-lg py-2 px-1 text-center">
                            <div class="text-lg font-bold text-success leading-none">
                                {{ $municipio->expedientes_aprobados_count }}</div>
                            <div class="text-[10px] text-base-content/50 uppercase mt-1">Aprobados</div>
                        </div>
                    </div>

                    {{-- Barra de progreso aprobados/total --}}
                    @if ($municipio->expedientes_count > 0)
                        <div class="w-full">
                            <div class="flex justify-between text-[10px] text-base-content/50 mb-0.5">
                                <span>Progreso de aprobación</span>
                                <span>{{ $municipio->expedientes_count > 0 ? round(($municipio->expedientes_aprobados_count / $municipio->expedientes_count) * 100) : 0 }}%</span>
                            </div>
                            <progress class="progress progress-success w-full h-1.5"
                                value="{{ $municipio->expedientes_aprobados_count }}"
                                max="{{ $municipio->expedientes_count }}"></progress>
                        </div>
                    @endif

                    {{-- Contacto --}}
                    <div class="space-y-1">
                        @if ($municipio->contacto_nombre)
                            <div class="flex items-center gap-2 text-xs">
                                <x-heroicon-o-user class="w-3.5 h-3.5 text-base-content/40 shrink-0" />
                                <span class="truncate">{{ $municipio->contacto_nombre }}</span>
                            </div>
                        @endif
                        @if ($municipio->contacto_email)
                            <div class="flex items-center gap-2 text-xs">
                                <x-heroicon-o-envelope class="w-3.5 h-3.5 text-base-content/40 shrink-0" />
                                <span class="truncate">{{ $municipio->contacto_email }}</span>
                            </div>
                        @endif
                        @if (!$municipio->contacto_nombre && !$municipio->contacto_email)
                            <div class="flex items-center gap-2 text-xs text-base-content/30 italic">
                                <x-heroicon-o-user class="w-3.5 h-3.5 shrink-0" />
                                Sin contacto registrado
                            </div>
                        @endif
                    </div>

                    {{-- Usuarios asignados --}}
                    <div class="border-t border-base-200 pt-2 mt-auto">
                        @if ($municipio->users->isNotEmpty())
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 flex-wrap">
                                    @foreach ($municipio->users->take(3) as $usuario)
                                        <div class="tooltip tooltip-top"
                                            data-tip="{{ $usuario->nombre_completo }} ({{ $usuario->role->nombre }})">
                                            <div class="avatar placeholder">
                                                <div class="bg-neutral text-neutral-content rounded-full w-6 h-6 flex items-center justify-center">
                                                    <span class="text-[10px]">{{ $usuario->iniciales }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($municipio->users->count() > 3)
                                        <span class="badge badge-ghost badge-xs">
                                            +{{ $municipio->users->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                                <x-heroicon-o-arrow-right
                                    class="w-4 h-4 text-base-content/30 group-hover:text-primary transition-colors" />
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-base-content/30 italic">Sin usuarios asignados</p>
                                <x-heroicon-o-arrow-right
                                    class="w-4 h-4 text-base-content/30 group-hover:text-primary transition-colors" />
                            </div>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body items-center text-center py-16">
                        <div class="bg-base-200 rounded-full p-4 mb-2">
                            <x-heroicon-o-building-library class="w-10 h-10 text-base-content/30" />
                        </div>
                        <h3 class="font-semibold text-base-content/60">No se encontraron municipios</h3>
                        @if ($search)
                            <p class="text-sm text-base-content/40">Intenta con otro término de búsqueda</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
