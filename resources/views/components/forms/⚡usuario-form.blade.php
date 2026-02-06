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
    public $roleIdOriginal = ''; // Para detectar cambio de rol en edición
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

    // Computed: Rol original del usuario (para detectar cambios)
    #[Computed]
    public function rolOriginal()
    {
        return $this->roleIdOriginal ? Role::find($this->roleIdOriginal) : null;
    }

    // Computed: Verificar si se puede cambiar el rol (solo si no tiene municipios en historial)
    #[Computed]
    public function puedesCambiarRol()
    {
        if (!$this->usuarioId) {
            return true; // Usuario nuevo, puede elegir cualquier rol
        }

        $usuario = User::find($this->usuarioId);
        if (!$usuario) {
            return true;
        }

        // Si el rol original requiere municipios, verificar si tiene historial
        if ($this->rolOriginal?->requiereMunicipios()) {
            return !$usuario->tieneMunicipiosEnHistorial();
        }

        return true;
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
            $this->roleIdOriginal = $usuario->role_id; // Guardar rol original

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
        // Si no puede cambiar el rol, revertir al original
        if (!$this->puedesCambiarRol && $this->rolOriginal?->requiereMunicipios()) {
            $nuevoRol = Role::find($this->roleId);
            // Solo bloquear si intenta cambiar entre Municipal/Técnico
            if ($nuevoRol?->requiereMunicipios() && $this->roleId != $this->roleIdOriginal) {
                $this->roleId = $this->roleIdOriginal;
                $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: 'No se puede cambiar el rol porque el usuario ya tiene municipios asignados en su historial.');
                return;
            }
        }

        $this->municipiosSeleccionados = [];
        $this->municipioSeleccionado = '';
        $this->resetErrorBag('municipiosSeleccionados');
        $this->resetErrorBag('municipioSeleccionado');
    }

    // Guardar
    public function guardar()
    {
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
                $usuario->role_id = $this->roleId;

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
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Nombres <span class="text-error">*</span></span>
                </label>
                <input type="text" wire:model="nombres" wire:keydown="clearError('nombres')"
                    placeholder="Ingrese los nombres"
                    class="input input-bordered w-full @error('nombres') input-error @enderror" />
                @error('nombres')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- Apellidos -->
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Apellidos <span class="text-error">*</span></span>
                </label>
                <input type="text" wire:model="apellidos" wire:keydown="clearError('apellidos')"
                    placeholder="Ingrese los apellidos"
                    class="input input-bordered w-full @error('apellidos') input-error @enderror" />
                @error('apellidos')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- Cargo -->
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Cargo</span>
                    <span class="label-text-alt text-base-content/50">Opcional</span>
                </label>
                <input type="text" wire:model="cargo" placeholder="Ej: Coordinador de Proyectos"
                    class="input input-bordered w-full" />
            </div>

            <!-- Teléfono -->
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Teléfono</span>
                    <span class="label-text-alt text-base-content/50">8 dígitos</span>
                </label>
                <input type="text" wire:model="telefono" maxlength="8" placeholder="12345678"
                    class="input input-bordered w-full" />
            </div>

            <!-- Email -->
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Correo Electrónico <span class="text-error">*</span></span>
                </label>
                <label class="input input-bordered flex items-center gap-2 @error('email') input-error @enderror">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                        class="w-4 h-4 opacity-70">
                        <path
                            d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z" />
                        <path
                            d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z" />
                    </svg>
                    <input type="email" wire:model="email" wire:keydown="clearError('email')" class="grow"
                        placeholder="correo@ejemplo.com" />
                </label>
                @error('email')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            @if (!$this->rolSeleccionado?->esAdmin())
                <!-- Contraseña -->
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">
                            Contraseña
                            @if (!$usuarioId)
                                <span class="text-error">*</span>
                            @endif
                        </span>
                        @if ($usuarioId)
                            <span class="label-text-alt text-base-content/50">Dejar vacío para mantener</span>
                        @endif
                    </label>
                    <label
                        class="input input-bordered flex items-center gap-2 @error('password') input-error @enderror">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                            class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd"
                                d="M14 6a4 4 0 0 1-4.899 3.899l-1.955 1.955a.5.5 0 0 1-.353.146H5v1.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2.293a.5.5 0 0 1 .146-.353l3.955-3.955A4 4 0 1 1 14 6Zm-4-2a.75.75 0 0 0 0 1.5.5.5 0 0 1 .5.5.75.75 0 0 0 1.5 0 2 2 0 0 0-2-2Z"
                                clip-rule="evenodd" />
                        </svg>
                        <input type="password" wire:model="password" wire:keydown="clearError('password')"
                            class="grow" placeholder="Mínimo 8 caracteres" />
                    </label>
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Rol -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">
                        Rol<span class="text-error">*</span>
                    </legend>
                    <select wire:model.live="roleId" wire:change="clearError('roleId')"
                        class="select select-bordered w-full @error('roleId') select-error @enderror"
                        @if ($usuarioId && $this->rolOriginal?->requiereMunicipios() && !$this->puedesCambiarRol) disabled @endif>
                        <option value="" disabled selected>Seleccione un rol</option>
                        @foreach ($this->roles as $rol)
                            <option value="{{ $rol->id }}" @if (
                                $usuarioId &&
                                    $this->rolOriginal?->requiereMunicipios() &&
                                    !$this->puedesCambiarRol &&
                                    $rol->requiereMunicipios() &&
                                    $rol->id != $this->roleIdOriginal) disabled @endif>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('roleId')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                    @if ($usuarioId && $this->rolOriginal?->requiereMunicipios() && !$this->puedesCambiarRol)
                        <label class="label">
                            <span class="label-text-alt text-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                No se puede cambiar el rol porque este usuario tiene municipios asignados en su
                                historial.
                            </span>
                        </label>
                    @endif
                </fieldset>
            @endif

        </div>

        <!-- Municipios (solo para Técnico y Municipal) -->
        @if ($this->rolSeleccionado?->requiereMunicipios())
            <div class="form-control w-full mt-4">
                @if ($this->rolSeleccionado->esMunicipal())
                    {{-- Select simple para Municipal --}}
                    <label class="label">
                        <span class="label-text">
                            Municipio <span class="text-error">*</span>
                        </span>
                        <span class="label-text-alt text-base-content/50">Solo 1 municipio</span>
                    </label>
                    <select wire:model="municipioSeleccionado"
                        class="select select-bordered w-full @error('municipioSeleccionado') select-error @enderror">
                        <option value="">Seleccione un municipio</option>
                        @foreach ($this->municipiosDisponiblesMunicipal as $municipio)
                            <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
                        @endforeach
                    </select>
                    @error('municipioSeleccionado')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                    @if ($this->municipiosDisponiblesMunicipal->isEmpty())
                        <label class="label">
                            <span class="label-text-alt text-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Todos los municipios ya tienen un usuario Municipal activo asignado.
                            </span>
                        </label>
                    @endif
                @else
                    {{-- Checkboxes para Técnico --}}
                    <label class="label">
                        <span class="label-text">
                            Municipios Asignados <span class="text-error">*</span>
                        </span>
                        <span class="label-text-alt text-base-content/50">Seleccione uno o varios</span>
                    </label>

                    {{-- Badges de municipios seleccionados --}}
                    @if (!empty($this->nombresMunicipiosSeleccionados))
                        <div class="flex flex-wrap gap-2 mb-3 p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-base-content/70 mr-2">Seleccionados:</span>
                            @foreach ($this->nombresMunicipiosSeleccionados as $id => $nombre)
                                <span class="badge badge-primary gap-1">
                                    {{ $nombre }}
                                    {{-- pendiente --}}
                                    <button type="button" wire:click="quitarMunicipio({{ $id }})"
                                        class="btn btn-ghost btn-xs p-0 h-auto min-h-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Grid de checkboxes --}}
                    <div
                        class="border border-base-300 rounded-lg p-3 max-h-48 overflow-y-auto @error('municipiosSeleccionados') border-error @enderror">
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
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                    <label class="label">
                        <span class="label-text-alt text-base-content/50">
                            {{ count($this->municipiosSeleccionados) }} municipio(s) seleccionado(s)
                        </span>
                        <span class="label-text-alt text-warning text-xs">
                            Los municipios tachados ya están asignados a otro Técnico activo
                        </span>
                    </label>
                @endif
            </div>
        @endif

        <!-- Botones -->
        <div class="divider"></div>
        <div class="modal-action mt-0">
            <button type="button" wire:click="cancelar" class="btn btn-ghost">
                Cancelar
            </button>
            <button type="submit" class="btn {{ $usuarioId ? 'btn-warning' : 'btn-primary' }}"
                wire:loading.attr="disabled">
                @if ($usuarioId)
                    <span wire:loading.remove wire:target="guardar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                    Actualizar
                @else
                    <span wire:loading.remove wire:target="guardar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                    Guardar
                @endif
            </button>
        </div>
    </form>
</div>
