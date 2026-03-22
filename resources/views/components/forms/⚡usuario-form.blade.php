<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\User;
use App\Models\Role;
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;

new class extends Component {
    // ID del usuario (null = crear, int = editar)
    public $usuarioId = null;

    // Variables del formulario
    public $nombres = '';
    public $apellidos = '';
    public $cargo = '';
    public $telefono = '';
    public $email = '';
    public $password = '';
    public $roleId = '';
    public $municipiosSeleccionados = [];
    public $municipioSeleccionado = ''; // Para rol Municipal (select simple)

    // Mount: cargar usuario si existe
    public function mount($usuarioId = null)
    {
        $this->usuarioId = $usuarioId;

        if ($this->usuarioId) {
            $this->cargarUsuario($this->usuarioId);
        }
    }

    // Computed: Roles (excluir Administrador)
    #[Computed]
    public function roles()
    {
        $roles = Role::where('nombre', '!=', Role::ADMIN);

        // si ya hay un usuario con rol Director y Jefe_financiero, no mostrar el rol en la lista, pero si tiene estado inactivo, si puede seleccionarlo
        $roles = $roles->get()->filter(function ($rol) {
            if (in_array($rol->nombre, [Role::DIRECTOR, Role::JEFE_FINANCIERO])) {
                $existe = User::where('role_id', $rol->id)->activos()->exists();
                if ($existe) {
                    return false;
                }
            }
            return true;
        });
        return $roles;
    }

    // Computed: Municipios activos
    #[Computed]
    public function municipios()
    {
        return Municipio::activos()->ordenados()->get();
    }

    // Computed: Municipios disponibles para rol Municipal (excluir los que ya tienen usuario activo)
    #[Computed]
    public function municipiosDisponiblesMunicipal()
    {
        $municipiosOcupados = DB::table('usuario_municipio')->join('users', 'users.id', '=', 'usuario_municipio.user_id')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nombre', Role::MUNICIPAL)->where('users.estado', true)->where('usuario_municipio.estado', true)->when($this->usuarioId, fn($q) => $q->where('users.id', '!=', $this->usuarioId))->pluck('usuario_municipio.municipio_id')->toArray();

        return Municipio::activos()->ordenados()->whereNotIn('id', $municipiosOcupados)->get();
    }

    // Computed: Municipios disponibles para rol Técnico (marcar los que ya están asignados a otro técnico)
    #[Computed]
    public function municipiosConEstadoTecnico()
    {
        $municipiosOcupados = DB::table('usuario_municipio')->join('users', 'users.id', '=', 'usuario_municipio.user_id')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nombre', Role::TECNICO)->where('users.estado', true)->where('usuario_municipio.estado', true)->when($this->usuarioId, fn($q) => $q->where('users.id', '!=', $this->usuarioId))->pluck('usuario_municipio.municipio_id')->toArray();

        return Municipio::activos()
            ->ordenados()
            ->get()
            ->map(function ($municipio) use ($municipiosOcupados) {
                $municipio->ocupado = in_array($municipio->id, $municipiosOcupados);
                return $municipio;
            });
    }

    // Computed: Nombres de municipios seleccionados (para mostrar badges)
    #[Computed]
    public function nombresMunicipiosSeleccionados()
    {
        if (empty($this->municipiosSeleccionados)) {
            return [];
        }
        return Municipio::whereIn('id', $this->municipiosSeleccionados)->ordenados()->pluck('nombre', 'id')->toArray();
    }

    // Computed: Rol seleccionado (retorna el modelo Role)
    #[Computed]
    public function rolSeleccionado()
    {
        return $this->roleId ? Role::find($this->roleId) : null;
    }

    // Toggle municipio para técnico (checkbox)
    public function toggleMunicipio($municipioId)
    {
        $municipioId = (int) $municipioId;

        if (in_array($municipioId, $this->municipiosSeleccionados)) {
            $this->municipiosSeleccionados = array_values(array_diff($this->municipiosSeleccionados, [$municipioId]));
        } else {
            $this->municipiosSeleccionados[] = $municipioId;
        }

        $this->resetErrorBag('municipiosSeleccionados');
    }

    // Quitar municipio de la selección
    public function quitarMunicipio($municipioId)
    {
        $this->municipiosSeleccionados = array_values(array_diff($this->municipiosSeleccionados, [(int) $municipioId]));
    }

    // Cargar Usuario
    public function cargarUsuario($id)
    {
        // Buscar usuario
        $usuario = User::with('municipios')->find($id);

        // Asignar valores
        if ($usuario) {
            $this->usuarioId = $usuario->id;
            $this->nombres = $usuario->nombres;
            $this->apellidos = $usuario->apellidos;
            $this->cargo = $usuario->cargo ?? '';
            $this->telefono = $usuario->telefono ?? '';
            $this->email = $usuario->email;
            $this->roleId = $usuario->role_id;

            $municipiosIds = $usuario->municipios->pluck('id')->toArray();

            // Asignar según el tipo de rol
            if ($usuario->role->esMunicipal()) {
                $this->municipioSeleccionado = $municipiosIds[0] ?? '';
                $this->municipiosSeleccionados = [];
            } else {
                $this->municipiosSeleccionados = $municipiosIds;
                $this->municipioSeleccionado = '';
            }
        }
    }

    // Limpiar selección de municipios cuando cambia el rol
    public function updatedRoleId()
    {
        $this->municipiosSeleccionados = [];
        $this->municipioSeleccionado = '';
        $this->resetErrorBag('municipiosSeleccionados');
        $this->resetErrorBag('municipioSeleccionado');
    }

    // Guardar
    public function guardar()
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Acceso Denegado');
        }

        if ($this->usuarioId) {
            $usuarioActual = User::find($this->usuarioId);
            if (!$usuarioActual) {
                abort(404, 'Usuario no encontrado');
            }
            // En edición el rol es solo lectura y no puede cambiarse
            $this->roleId = $usuarioActual->role_id;
        }

        // Validación base
        $rules = [
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:8',
            'email' => 'required|email|max:255|unique:users,email,' . $this->usuarioId,
            'roleId' => 'required|exists:roles,id',
        ];

        // Password requerido solo si es nuevo usuario
        if (!$this->usuarioId) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        // Validar datos base
        $this->validate($rules, [
            'nombres.required' => 'Los nombres son requeridos.',
            'apellidos.required' => 'Los apellidos son requeridos.',
            'email.required' => 'El correo es requerido.',
            'email.email' => 'El correo debe ser válido.',
            'email.unique' => 'El correo ya está registrado.',
            'roleId.required' => 'Debe seleccionar un rol.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // Validar municipios según rol
        if ($this->rolSeleccionado?->requiereMunicipios()) {
            if ($this->rolSeleccionado->esMunicipal()) {
                // Validar que se haya seleccionado un municipio
                if (empty($this->municipioSeleccionado)) {
                    $this->addError('municipioSeleccionado', 'Debe seleccionar un municipio.');
                    return;
                }

                // Verificar que no exista otro usuario Municipal activo con el mismo municipio
                $existeMunicipal = DB::table('usuario_municipio')->join('users', 'users.id', '=', 'usuario_municipio.user_id')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nombre', Role::MUNICIPAL)->where('users.estado', true)->where('usuario_municipio.estado', true)->where('usuario_municipio.municipio_id', $this->municipioSeleccionado)->when($this->usuarioId, fn($q) => $q->where('users.id', '!=', $this->usuarioId))->exists();

                if ($existeMunicipal) {
                    $this->addError('municipioSeleccionado', 'Ya existe un usuario Municipal activo asignado a este municipio.');
                    return;
                }

                // Convertir a array para el sync
                $this->municipiosSeleccionados = [(int) $this->municipioSeleccionado];
            } elseif ($this->rolSeleccionado->esTecnico()) {
                // Validar que se haya seleccionado al menos un municipio
                if (empty($this->municipiosSeleccionados)) {
                    $this->addError('municipiosSeleccionados', 'Debe seleccionar al menos un municipio.');
                    return;
                }

                // Verificar que ningún municipio esté asignado a otro técnico activo
                $municipiosOcupadosPorOtro = DB::table('usuario_municipio')->join('users', 'users.id', '=', 'usuario_municipio.user_id')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nombre', Role::TECNICO)->where('users.estado', true)->where('usuario_municipio.estado', true)->whereIn('usuario_municipio.municipio_id', $this->municipiosSeleccionados)->when($this->usuarioId, fn($q) => $q->where('users.id', '!=', $this->usuarioId))->pluck('usuario_municipio.municipio_id')->toArray();

                if (!empty($municipiosOcupadosPorOtro)) {
                    $nombresMunicipios = Municipio::whereIn('id', $municipiosOcupadosPorOtro)->pluck('nombre')->join(', ');
                    $this->addError('municipiosSeleccionados', "Los siguientes municipios ya están asignados a otro Técnico activo: {$nombresMunicipios}");
                    return;
                }
            }
        }

        try {
            // Si se esta editando
            if ($this->usuarioId) {
                // Buscar usuario
                $usuario = User::find($this->usuarioId);

                $usuario->nombres = $this->nombres;
                $usuario->apellidos = $this->apellidos;
                $usuario->cargo = $this->cargo;
                $usuario->telefono = $this->telefono;
                $usuario->email = $this->email;

                if ($this->password) {
                    $usuario->password = bcrypt($this->password);
                }

                // Guardar
                $usuario->save();

                // Sync municipios con historial (soft delete)
                if ($this->rolSeleccionado?->requiereMunicipios()) {
                    $usuario->syncMunicipiosConHistorial($this->municipiosSeleccionados);
                } else {
                    $usuario->desactivarTodosMunicipios();
                }

                $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: '¡Usuario actualizado correctamente!');
            } else {
                $usuario = User::create([
                    'nombres' => $this->nombres,
                    'apellidos' => $this->apellidos,
                    'cargo' => $this->cargo,
                    'telefono' => $this->telefono,
                    'email' => $this->email,
                    'password' => bcrypt($this->password),
                    'role_id' => $this->roleId,
                ]);

                // Sync municipios con historial
                if ($this->rolSeleccionado?->requiereMunicipios()) {
                    $usuario->syncMunicipiosConHistorial($this->municipiosSeleccionados);
                }

                $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: '¡Usuario creado correctamente!');
            }

            // Emitir evento para refrescar tabla y cerrar modal
            $this->dispatch('usuario-guardado');
            $this->dispatch('cerrar-modal-usuario');
        } catch (\Exception $e) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al guardar: ' . $e->getMessage());
        }
    }

    // Cancelar - cerrar modal
    public function cancelar()
    {
        $this->dispatch('cerrar-modal-usuario');
    }

    // Limpiar error específico
    public function clearError($field)
    {
        $this->resetErrorBag($field);
    }
};
?>

<div>
    <form wire:submit="guardar" class="p-4 pt-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nombres -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs uppercase tracking-wide">Nombres <span class="text-error">*</span>
                </legend>
                <input type="text" wire:model="nombres" wire:keydown="clearError('nombres')"
                    placeholder="Ingrese los nombres"
                    class="input input-bordered w-full @error('nombres') input-error @enderror" />
                @error('nombres')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <!-- Apellidos -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs uppercase tracking-wide">Apellidos <span
                        class="text-error">*</span></legend>
                <input type="text" wire:model="apellidos" wire:keydown="clearError('apellidos')"
                    placeholder="Ingrese los apellidos"
                    class="input input-bordered w-full @error('apellidos') input-error @enderror" />
                @error('apellidos')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <!-- Cargo -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs uppercase tracking-wide">Cargo</legend>
                <input type="text" wire:model="cargo" placeholder="Ej: Coordinador de Proyectos"
                    class="input input-bordered w-full" />
                <p class="label text-base-content/50">Opcional</p>
            </fieldset>

            <!-- Teléfono -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs uppercase tracking-wide">Teléfono</legend>
                <input type="text" wire:model="telefono" maxlength="8" placeholder="12345678"
                    class="input input-bordered w-full" />
                <p class="label text-base-content/50">8 dígitos</p>
            </fieldset>

            <!-- Email -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs uppercase tracking-wide">Correo Electrónico <span
                        class="text-error">*</span></legend>
                <label class="input input-bordered w-full @error('email') input-error @enderror">
                    <x-heroicon-o-envelope class="h-[1em] opacity-50" />
                    <input type="email" wire:model="email" wire:keydown="clearError('email')" class="grow"
                        placeholder="correo@ejemplo.com" />
                </label>
                @error('email')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            @if (!$this->rolSeleccionado?->esAdmin())
                <!-- Contraseña -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">
                        Contraseña
                        @if (!$usuarioId)
                            <span class="text-error">*</span>
                        @endif
                    </legend>
                    <label class="input input-bordered w-full @error('password') input-error @enderror">
                        <x-heroicon-o-key class="h-[1em] opacity-50" />
                        <input type="password" wire:model="password" wire:keydown="clearError('password')"
                            class="grow" placeholder="Mínimo 8 caracteres" />
                    </label>
                    @if ($usuarioId)
                        <p class="label text-base-content/50">Dejar vacío para mantener</p>
                    @endif
                    @error('password')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endif

            <!-- Rol -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs uppercase tracking-wide">
                    Rol<span class="text-error">*</span>
                </legend>

                @if ($usuarioId)
                    <input type="text" class="input input-bordered w-full"
                        value="{{ $this->rolSeleccionado?->nombre }}" readonly disabled />
                @else
                    <select wire:model.live="roleId" wire:change="clearError('roleId')"
                        class="select select-bordered w-full @error('roleId') select-error @enderror">
                        <option value="" disabled selected>Seleccione un rol</option>
                        @foreach ($this->roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('roleId')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                @endif
            </fieldset>

        </div>

        <!-- Municipios (solo para Técnico y Municipal) -->
        @if ($this->rolSeleccionado?->requiereMunicipios())
            <fieldset class="fieldset mt-4">
                @if ($this->rolSeleccionado->esMunicipal())
                    {{-- Select simple para Municipal --}}
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">
                        Municipio <span class="text-error">*</span>
                    </legend>
                    <select wire:model="municipioSeleccionado"
                        class="select select-bordered w-full @error('municipioSeleccionado') select-error @enderror">
                        <option value="" selected disabled>Seleccione un municipio</option>
                        @foreach ($this->municipiosDisponiblesMunicipal as $municipio)
                            <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="label text-base-content/50">Solo 1 municipio</p>
                    @error('municipioSeleccionado')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                    @if ($this->municipiosDisponiblesMunicipal->isEmpty())
                        <p class="label text-warning">
                            <x-heroicon-o-exclamation-triangle class="h-4 w-4 inline mr-1" />
                            Todos los municipios ya tienen un usuario Municipal activo asignado.
                        </p>
                    @endif
                @else
                    {{-- Checkboxes para Técnico --}}
                    <legend class="fieldset-legend text-xs uppercase tracking-wide">
                        Municipios Asignados <span class="text-error">*</span>
                    </legend>

                    {{-- Badges de municipios seleccionados --}}
                    @if (!empty($this->nombresMunicipiosSeleccionados))
                        <div
                            class="flex flex-wrap gap-2 mb-3 p-3 bg-base-200/50 border border-base-content/10 rounded-lg">
                            <span class="text-sm text-base-content/70 mr-2">Seleccionados:</span>
                            @foreach ($this->nombresMunicipiosSeleccionados as $id => $nombre)
                                <span class="badge badge-soft badge-primary gap-1">
                                    {{ $nombre }}
                                    {{-- pendiente --}}
                                    <button type="button" wire:click="quitarMunicipio({{ $id }})"
                                        class="btn btn-ghost btn-xs p-0 h-auto min-h-0">
                                        <x-heroicon-o-x-mark class="h-3 w-3" />
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Grid de checkboxes --}}
                    <div @class([
                        'border rounded-lg p-3 max-h-48 overflow-y-auto',
                        'border-error' => $errors->has('municipiosSeleccionados'),
                        'border-base-content/10' => !$errors->has('municipiosSeleccionados'),
                    ])>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach ($this->municipiosConEstadoTecnico as $municipio)
                                <label
                                    class="flex items-center gap-2 cursor-pointer p-2 rounded hover:bg-base-200 transition-colors
                                    {{ $municipio->ocupado ? 'opacity-50' : '' }}">
                                    <input type="checkbox" wire:click="toggleMunicipio({{ $municipio->id }})"
                                        {{ in_array($municipio->id, $this->municipiosSeleccionados) ? 'checked' : '' }}
                                        {{ $municipio->ocupado ? 'disabled' : '' }}
                                        class="checkbox checkbox-primary checkbox-sm" />
                                    <span class="label-text text-sm {{ $municipio->ocupado ? 'line-through' : '' }}">
                                        {{ $municipio->nombre }}
                                        @if ($municipio->ocupado)
                                            <span class="text-xs text-warning">(asignado)</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @error('municipiosSeleccionados')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                    <p class="label text-base-content/50">
                        {{ count($this->municipiosSeleccionados) }} municipio(s) seleccionado(s)
                        — <span class="text-warning text-xs">Los municipios tachados ya están asignados a otro Técnico
                            activo</span>
                    </p>
                @endif
            </fieldset>
        @endif

        <!-- Botones -->
        <div class="divider"></div>
        <div class="modal-action mt-0">
            <button type="button" wire:click="cancelar" class="btn btn-ghost btn-sm">
                Cancelar
            </button>
            <button type="submit" class="btn btn-sm {{ $usuarioId ? 'btn-warning' : 'btn-primary' }}"
                wire:loading.attr="disabled">
                @if ($usuarioId)
                    <span wire:loading.remove wire:target="guardar">
                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                    </span>
                    <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                    Actualizar
                @else
                    <span wire:loading.remove wire:target="guardar">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                    </span>
                    <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                    Guardar
                @endif
            </button>
        </div>
    </form>
</div>
