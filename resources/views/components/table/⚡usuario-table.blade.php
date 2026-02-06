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
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Activo
                                        </span>
                                    @else
                                        <button wire:click="cambiarEstado({{ $usuario->id }})"
                                            class="badge badge-sm cursor-pointer transition-all hover:scale-105 {{ $usuario->estaActivo() ? 'badge-success' : 'badge-error' }}">
                                            @if ($usuario->estaActivo())
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
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-center items-center gap-1">
                                    <!-- Ver -->
                                    <div class="tooltip" data-tip="Ver detalles">
                                        <a href="{{ route('admin.usuarios.show', $usuario->id) }}"
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
                                    <div class="tooltip" data-tip="Editar">
                                        <button wire:click="editar({{ $usuario->id }})"
                                            class="btn btn-ghost btn-sm btn-square text-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Eliminar (solo si no es Admin) -->
                                    @if (!$usuario->isAdmin())
                                        <div class="tooltip" data-tip="Eliminar">
                                            <button wire:click="confirmarEliminar({{ $usuario->id }})"
                                                class="btn btn-ghost btn-sm btn-square text-error">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        class="w-12 h-12 text-base-content/30">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
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
