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
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
        <table class="table table-zebra table-sm">
            <thead>
                <tr class="bg-base-200">
                    <th class="text-center w-12">No.</th>
                    <th class="min-w-36">Municipio</th>
                    <th class="min-w-36">Contacto</th>
                    <th class="text-center whitespace-nowrap">Teléfono</th>
                    <th class="text-center min-w-28">Usuarios</th>
                    <th class="text-center whitespace-nowrap">Expedientes</th>
                    <th class="text-center whitespace-nowrap">Estado</th>
                    <th class="text-center whitespace-nowrap">Acciones</th>
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
                        <td class="text-center whitespace-nowrap">
                            @if ($municipio->contacto_telefono)
                                <span class="text-sm">{{ $municipio->contacto_telefono }}</span>
                            @else
                                <span class="text-base-content/40">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $municipal = $municipio->users->first(fn($u) => $u->role->nombre === Role::MUNICIPAL);
                                $tecnicos = $municipio->users->filter(fn($u) => $u->role->nombre === Role::TECNICO);
                            @endphp
                            @if ($municipal || $tecnicos->isNotEmpty())
                                <div class="flex flex-col items-center gap-0.5">
                                    @if ($municipal)
                                        <span class="badge badge-xs badge-ghost"
                                            title="Municipal: {{ $municipal->nombre_completo }}">
                                            <x-heroicon-o-user class="w-3 h-3 mr-0.5" />
                                            {{ Str::limit($municipal->nombre_completo, 15) }}
                                        </span>
                                    @endif
                                    @if ($tecnicos->isNotEmpty())
                                        <span class="badge badge-xs badge-info badge-outline"
                                            title="Técnico(s): {{ $tecnicos->pluck('nombre_completo')->join(', ') }}">
                                            <x-heroicon-o-wrench class="w-3 h-3 mr-0.5" />
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
                                        class="badge badge-sm cursor-pointer transition-all hover:scale-105 gap-1 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                                        <div
                                            class="status {{ $municipio->estaActivo() ? 'status-success' : 'status-error' }} status-xs">
                                        </div>
                                        {{ $municipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </div>
                            @else
                                <span
                                    class="badge badge-sm gap-1 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                                    <div
                                        class="status {{ $municipio->estaActivo() ? 'status-success' : 'status-error' }} status-xs">
                                    </div>
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
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </a>
                                </div>

                                <!-- Editar -->
                                @if (auth()->user()->isAdmin())
                                    <div class="tooltip" data-tip="Editar contacto">
                                        <button wire:click="editar({{ $municipio->id }})"
                                            class="btn btn-ghost btn-sm btn-square text-warning">
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
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
                                <x-heroicon-o-building-office-2 class="w-12 h-12 text-base-content/30" />
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
        <div class="px-4 py-3">
            {{ $this->municipios->links() }}
        </div>
    @endif
</div>
