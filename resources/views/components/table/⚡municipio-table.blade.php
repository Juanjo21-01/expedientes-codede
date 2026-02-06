<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Models\Municipio;
use App\Models\User;
use App\Models\Role;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    // Props reactivos desde el index
    #[Reactive]
    public $search = '';

    #[Reactive]
    public $estadoFiltro = '';

    public $perPage = 15;

    // Computed: Municipios paginados con filtros
    #[Computed]
    public function municipios()
    {
        return Municipio::query()
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->estadoFiltro === 'activo', fn($q) => $q->activos())
            ->when($this->estadoFiltro === 'inactivo', fn($q) => $q->inactivos())
            ->ordenados()
            ->withCount(['expedientes', 'expedientes as expedientes_activos_count' => fn($q) => $q->where('estado', 'activo')])
            ->with(['users' => fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('nombre', [Role::MUNICIPAL, Role::TECNICO]))->where('users.estado', true)])
            ->paginate($this->perPage);
    }

    // Escuchar eventos para refrescar
    #[On('municipio-guardado')]
    public function refrescar()
    {
        unset($this->municipios);
    }

    // Cambiar estado del municipio (solo Admin)
    public function cambiarEstado($id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $municipio = Municipio::withCount(['expedientes as expedientes_activos_count' => fn($q) => $q->where('estado', 'activo')])->find($id);

        if (!$municipio) {
            return;
        }

        // Si se intenta DESACTIVAR, advertir si tiene expedientes activos
        if ($municipio->estaActivo()) {
            if ($municipio->expedientes_activos_count > 0) {
                $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: "El municipio '{$municipio->nombre}' tiene {$municipio->expedientes_activos_count} expediente(s) activo(s). Se recomienda resolver los expedientes antes de desactivarlo.");
            }

            // Advertir si tiene usuarios asignados activos
            $usuariosActivos = $municipio->users()->where('users.estado', true)->whereHas('role', fn($q) => $q->whereIn('nombre', [Role::MUNICIPAL, Role::TECNICO]))->count();

            if ($usuariosActivos > 0) {
                $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: "El municipio '{$municipio->nombre}' tiene {$usuariosActivos} usuario(s) activo(s) asignado(s).");
            }

            $municipio->desactivar();
        } else {
            $municipio->activar();
        }

        // Refrescar lista
        unset($this->municipios);
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Estado de '{$municipio->nombre}' actualizado correctamente.");
    }

    // Emitir evento para editar (solo Admin)
    public function editar($id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->dispatch('abrir-modal-municipio', municipioId: $id);
    }

    // Reset página cuando cambian filtros
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedEstadoFiltro()
    {
        $this->resetPage();
    }
};
?>

<div>
    <!-- Tabla -->
    <div class="card bg-base-100 shadow-sm border border-base-300">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-pin-rows">
                <thead>
                    <tr class="bg-base-200">
                        <th class="text-center">No.</th>
                        <th>Municipio</th>
                        <th>Contacto</th>
                        <th class="text-center">Teléfono</th>
                        <th class="text-center">Usuarios</th>
                        <th class="text-center">Expedientes</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->municipios as $index => $municipio)
                        <tr class="hover">
                            <td class="text-center font-medium">{{ $this->municipios->firstItem() + $index }}</td>
                            <td>
                                <div>
                                    <div class="font-bold">{{ $municipio->nombre }}</div>
                                    <div class="text-xs opacity-60">{{ $municipio->departamento }}</div>
                                </div>
                            </td>
                            <td>
                                @if ($municipio->contacto_nombre)
                                    <div class="text-sm font-medium">{{ $municipio->contacto_nombre }}</div>
                                    @if ($municipio->contacto_email)
                                        <div class="text-xs opacity-60">{{ $municipio->contacto_email }}</div>
                                    @endif
                                @else
                                    <span class="text-base-content/40 text-sm italic">Sin contacto</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($municipio->contacto_telefono)
                                    <span class="text-sm">{{ $municipio->contacto_telefono }}</span>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $municipal = $municipio->users->first(
                                        fn($u) => $u->role->nombre === Role::MUNICIPAL,
                                    );
                                    $tecnicos = $municipio->users->filter(fn($u) => $u->role->nombre === Role::TECNICO);
                                @endphp
                                @if ($municipal || $tecnicos->isNotEmpty())
                                    <div class="flex flex-col items-center gap-0.5">
                                        @if ($municipal)
                                            <span class="badge badge-xs badge-ghost"
                                                title="Municipal: {{ $municipal->nombre_completo }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-3 h-3 mr-0.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                                {{ Str::limit($municipal->nombre_completo, 15) }}
                                            </span>
                                        @endif
                                        @if ($tecnicos->isNotEmpty())
                                            <span class="badge badge-xs badge-info badge-outline"
                                                title="Técnico(s): {{ $tecnicos->pluck('nombre_completo')->join(', ') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-3 h-3 mr-0.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11.42 15.17l-5.646 3.013a1.724 1.724 0 01-2.573-1.066l-.29-1.16a1.723 1.723 0 00-1.334-1.334l-1.16-.29a1.724 1.724 0 01-1.066-2.573l3.014-5.647a1.14 1.14 0 011.272-.503l3.693.738a1.14 1.14 0 00.95-.228l2.672-2.229a1.14 1.14 0 011.272-.042l.582.332a1.14 1.14 0 01.482.53l.738 1.846a1.14 1.14 0 00.665.665l1.846.738a1.14 1.14 0 01.53.482l.332.582a1.14 1.14 0 01-.042 1.272L16.4 12.928a1.14 1.14 0 00-.228.95l.738 3.693a1.14 1.14 0 01-.503 1.272l-5.647 3.014" />
                                                </svg>
                                                {{ $tecnicos->count() }} técnico(s)
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    @if ($municipio->expedientes_count > 0)
                                        <span class="badge badge-sm badge-outline">
                                            {{ $municipio->expedientes_count }} total
                                        </span>
                                    @endif
                                    @if ($municipio->expedientes_activos_count > 0)
                                        <span class="badge badge-sm badge-primary badge-outline">
                                            {{ $municipio->expedientes_activos_count }} activos
                                        </span>
                                    @endif
                                    @if ($municipio->expedientes_count === 0)
                                        <span class="text-base-content/40">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if (auth()->user()->isAdmin())
                                    <div class="tooltip" data-tip="Cambiar estado">
                                        <button wire:click="cambiarEstado({{ $municipio->id }})"
                                            class="badge badge-sm cursor-pointer transition-all hover:scale-105 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                                            @if ($municipio->estaActivo())
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3 h-3 mr-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Activo
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3 h-3 mr-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                Inactivo
                                            @endif
                                        </button>
                                    </div>
                                @else
                                    <span
                                        class="badge badge-sm {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                                        {{ $municipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-center items-center gap-1">
                                    <!-- Ver detalles -->
                                    <div class="tooltip" data-tip="Ver detalles">
                                        <a href="{{ route('admin.municipios.show', $municipio->id) }}"
                                            class="btn btn-ghost btn-sm btn-square text-info" wire:navigate>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Editar -->
                                    @if (auth()->user()->isAdmin())
                                        <div class="tooltip" data-tip="Editar contacto">
                                            <button wire:click="editar({{ $municipio->id }})"
                                                class="btn btn-ghost btn-sm btn-square text-warning">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        class="w-12 h-12 text-base-content/30">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 21h19.5M3.75 3v18m16.5-18v18M5.25 3h13.5M5.25 21h13.5M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                    <span class="text-base-content/50">No se encontraron municipios</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($this->municipios->hasPages())
            <div class="border-t border-base-300 px-4 py-3 bg-base-200/50">
                {{ $this->municipios->links() }}
            </div>
        @endif
    </div>
</div>
