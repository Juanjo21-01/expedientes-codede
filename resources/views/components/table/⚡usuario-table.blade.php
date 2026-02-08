<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Models\User;
use App\Models\Role;
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    // Props reactivos desde el index
    #[Reactive]
    public $search = '';

    #[Reactive]
    public $rolFiltro = '';

    public $perPage = 10;

    // Computed: Usuarios paginados con filtros
    #[Computed]
    public function usuarios()
    {
        return User::query()
            ->when(
                $this->search,
                fn($q) => $q->where(
                    fn($q) => $q
                        ->where('nombres', 'like', "%{$this->search}%")
                        ->orWhere('apellidos', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%"),
                ),
            )
            ->when($this->rolFiltro, fn($q) => $q->where('role_id', $this->rolFiltro))
            ->with(['role', 'municipios'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    // Escuchar eventos para refrescar
    #[On('usuario-guardado')]
    #[On('usuario-eliminado')]
    public function refrescar()
    {
        unset($this->usuarios);
    }

    // Cambiar estado del usuario
    public function cambiarEstado($id)
    {
        $usuario = User::find($id);

        // No permitir cambiar estado del Administrador
        if ($usuario->isAdmin()) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No puedes cambiar el estado de un Administrador.');
            return;
        }

        // Si se intenta ACTIVAR, verificar restricciones de roles únicos
        if ($usuario->estaInactivo()) {
            // Director: solo puede haber 1 activo
            if ($usuario->isDirector()) {
                $existeDirectorActivo = User::activos()->deRol(Role::DIRECTOR)->where('id', '!=', $usuario->id)->exists();

                if ($existeDirectorActivo) {
                    $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: 'Ya existe un Director General activo. Desactívalo primero.');
                    return;
                }
            }

            // Jefe Financiero: solo puede haber 1 activo
            if ($usuario->isJefeFinanciero()) {
                $existeJefeActivo = User::activos()->deRol(Role::JEFE_FINANCIERO)->where('id', '!=', $usuario->id)->exists();

                if ($existeJefeActivo) {
                    $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: 'Ya existe un Jefe Administrativo-Financiero activo. Desactívalo primero.');
                    return;
                }
            }

            // Municipal: verificar que su municipio no esté asignado a otro Municipal activo
            if ($usuario->isMunicipal()) {
                $municipiosUsuario = $usuario->municipios()->pluck('municipios.id')->toArray();

                if (!empty($municipiosUsuario)) {
                    $municipioOcupado = DB::table('usuario_municipio')->join('users', 'users.id', '=', 'usuario_municipio.user_id')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nombre', Role::MUNICIPAL)->where('users.estado', true)->where('usuario_municipio.estado', true)->where('users.id', '!=', $usuario->id)->whereIn('usuario_municipio.municipio_id', $municipiosUsuario)->first();

                    if ($municipioOcupado) {
                        $nombreMunicipio = Municipio::find($municipioOcupado->municipio_id)?->nombre ?? 'Desconocido';
                        $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: "El municipio '{$nombreMunicipio}' ya tiene otro usuario Municipal activo asignado.");
                        return;
                    }
                }
            }

            // Técnico: verificar que sus municipios no estén asignados a otro Técnico activo
            if ($usuario->isTecnico()) {
                $municipiosUsuario = $usuario->municipios()->pluck('municipios.id')->toArray();

                if (!empty($municipiosUsuario)) {
                    $municipiosOcupados = \DB::table('usuario_municipio')->join('users', 'users.id', '=', 'usuario_municipio.user_id')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nombre', Role::TECNICO)->where('users.estado', true)->where('usuario_municipio.estado', true)->where('users.id', '!=', $usuario->id)->whereIn('usuario_municipio.municipio_id', $municipiosUsuario)->pluck('usuario_municipio.municipio_id')->toArray();

                    if (!empty($municipiosOcupados)) {
                        $nombresMunicipios = Municipio::whereIn('id', $municipiosOcupados)->pluck('nombre')->join(', ');
                        $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: "Los siguientes municipios ya están asignados a otro Técnico activo: {$nombresMunicipios}");
                        return;
                    }
                }
            }
        }

        // Cambiar estado
        if ($usuario->estaActivo()) {
            $usuario->desactivar();
        } else {
            $usuario->activar();
        }

        // Refrescar lista
        unset($this->usuarios);
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Estado actualizado correctamente.');
    }

    // Emitir evento para editar
    public function editar($id)
    {
        $this->dispatch('abrir-modal-usuario', usuarioId: $id);
    }

    // Emitir evento para eliminar
    public function confirmarEliminar($id)
    {
        $this->dispatch('abrir-modal-eliminar', usuarioId: $id);
    }

    // Reset página cuando cambian filtros
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRolFiltro()
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
                        <th>Usuario</th>
                        <th>Correo Electrónico</th>
                        <th class="text-center">Rol</th>
                        <th class="text-center">Municipios</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->usuarios as $index => $usuario)
                        <tr class="hover">
                            <td class="text-center font-medium">{{ $this->usuarios->firstItem() + $index }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div
                                            class="bg-neutral text-neutral-content rounded-full w-10 h-10 flex items-center justify-center">
                                            <span class="text-sm">{{ $usuario->iniciales }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $usuario->nombre_completo }}</div>
                                        @if ($usuario->cargo)
                                            <div class="text-sm opacity-60">{{ $usuario->cargo }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $usuario->email }}</span>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge badge-sm badge-soft badge-outline 
                                    @if ($usuario->isAdmin()) badge-secondary
                                    @elseif($usuario->isDirector()) badge-primary
                                    @elseif($usuario->isJefeFinanciero()) badge-warning
                                    @elseif($usuario->isTecnico()) badge-info
                                    @else badge-ghost @endif">
                                    {{ $usuario->role->nombre }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($usuario->municipios->isNotEmpty())
                                    <span class="text-xs">{{ $usuario->municipios->pluck('nombre')->join(', ') }}</span>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="tooltip" data-tip="Cambiar estado">
                                    @if ($usuario->isAdmin())
                                        <span class="badge badge-success badge-sm gap-1">
                                            <x-heroicon-o-check-circle class="w-3 h-3" />
                                            Activo
                                        </span>
                                    @else
                                        <button wire:click="cambiarEstado({{ $usuario->id }})"
                                            class="badge badge-sm cursor-pointer transition-all hover:scale-105 {{ $usuario->estaActivo() ? 'badge-success' : 'badge-error' }}">
                                            @if ($usuario->estaActivo())
                                                <x-heroicon-o-check-circle class="w-3 h-3 mr-1" />
                                                Activo
                                            @else
                                                <x-heroicon-o-no-symbol class="w-3 h-3 mr-1" />
                                                Inactivo
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-center items-center gap-1">
                                    <!-- Ver -->
                                    <div class="tooltip" data-tip="Ver detalles">
                                        <a href="{{ route('admin.usuarios.show', $usuario->id) }}"
                                            class="btn btn-ghost btn-sm btn-square text-info" wire:navigate>
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </a>
                                    </div>

                                    <!-- Editar -->
                                    <div class="tooltip" data-tip="Editar">
                                        <button wire:click="editar({{ $usuario->id }})"
                                            class="btn btn-ghost btn-sm btn-square text-warning">
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        </button>
                                    </div>

                                    <!-- Eliminar (solo si no es Admin) -->
                                    @if (!$usuario->isAdmin())
                                        <div class="tooltip" data-tip="Eliminar">
                                            <button wire:click="confirmarEliminar({{ $usuario->id }})"
                                                class="btn btn-ghost btn-sm btn-square text-error">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-users class="w-12 h-12 text-base-content/30" />
                                    <span class="text-base-content/50">No se encontraron usuarios</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($this->usuarios->hasPages())
            <div class="border-t border-base-300 px-4 py-3 bg-base-200/50">
                {{ $this->usuarios->links() }}
            </div>
        @endif
    </div>
