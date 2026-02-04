<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\User;
use App\Models\Role;
use App\Models\Municipio;

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

    // Computed: Rol seleccionado (retorna el modelo Role)
    #[Computed]
    public function rolSeleccionado()
    {
        return $this->roleId ? Role::find($this->roleId) : null;
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
            $this->municipiosSeleccionados = $usuario->municipios->pluck('id')->toArray();
        }
    }

    // Guardar
    public function guardar()
    {
        // Validación
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

        // Validar datos
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
        // if (in_array($rolNombre, ['Técnico', 'Municipal'])) {
        //     if (empty($this->municipiosSeleccionados)) {
        //         $this->addError('municipiosSeleccionados', 'Debe seleccionar al menos un municipio.');
        //         return;
        //     }

        //     if ($rolNombre === 'Municipal' && count($this->municipiosSeleccionados) !== 1) {
        //         $this->addError('municipiosSeleccionados', 'El rol Municipal solo puede tener un municipio asignado.');
        //         return;
        //     }

        //     // Verificar que no exista otro Municipal con el mismo municipio
        //     if ($rolNombre === 'Municipal') {
        //         $existe = User::where('id', '!=', $this->usuarioId ?? 0)
        //             ->whjJereHas('municipios', fn($q) => $q->whereIn('municipio_id', $this->municipiosSeleccionados))
        //             ->whereHas('role', fn($q) => $q->where('nombre', 'Municipal'))
        //             ->exists();

        //         if ($existe) {
        //             $this->addError('municipiosSeleccionados', 'Ya existe un usuario Municipal asignado a este municipio.');
        //             return;
        //         }
        //     }
        // }

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

                // Sync municipios
                if ($this->rolSeleccionado?->requiereMunicipios()) {
                    $usuario->municipios()->sync($this->municipiosSeleccionados);
                } else {
                    $usuario->municipios()->detach();
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

                // Sync municipios
                if ($this->rolSeleccionado?->requiereMunicipios()) {
                    $usuario->municipios()->sync($this->municipiosSeleccionados);
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
                        class="select select-bordered w-full @error('roleId') select-error @enderror">
                        <option value="" disabled selected>Seleccione un rol</option>
                        @foreach ($this->roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('roleId')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </fieldset>
            @endif

        </div>

        <!-- Municipios (solo para Técnico y Municipal) -->
        @if ($this->rolSeleccionado?->requiereMunicipios())
            <div class="form-control w-full mt-4">
                <label class="label">
                    <span class="label-text">
                        {{ $this->rolSeleccionado->esMunicipal() ? 'Municipio' : 'Municipios Asignados' }}
                        <span class="text-error">*</span>
                    </span>
                    <span class="label-text-alt text-base-content/50">
                        {{ $this->rolSeleccionado->esMunicipal() ? 'Solo 1 municipio' : 'Puede seleccionar varios' }}
                    </span>
                </label>
                <select wire:model="municipiosSeleccionados" {{ $this->rolSeleccionado->esTecnico() ? 'multiple' : '' }}
                    class="select select-bordered w-full @error('municipiosSeleccionados') select-error @enderror"
                    {{ $this->rolSeleccionado->esTecnico() ? 'size=4' : '' }}>
                    @if (!$this->rolSeleccionado->esTecnico())
                        <option value="" disabled selected>Seleccione un municipio</option>
                    @endif
                    @foreach ($this->municipios as $municipio)
                        <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
                    @endforeach
                </select>
                @error('municipiosSeleccionados')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror

                @if ($this->rolSeleccionado->esTecnico())
                    <label class="label">
                        <span class="label-text-alt">
                            <kbd class="kbd kbd-sm">Ctrl</kbd> + Click para seleccionar varios
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
            <button type="submit" class="btn {{ $usuarioId ? 'btn-warning' : 'btn-primary' }}">
                @if ($usuarioId)
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                    Actualizar
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Guardar
                @endif
            </button>
        </div>
    </form>
</div>
