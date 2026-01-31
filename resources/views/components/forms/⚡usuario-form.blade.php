<?php

use Livewire\Component;

new class extends Component {
    // Props (pasadas desde page)
     $user = $attributes['user'] ?? new \App\Models\User(['estado' => true]);
     $isEdit = $user->exists;
     $rolNombre = $user->rol_id ? \App\Models\Rol::find($user->rol_id)->nombre : null;
     $municipios = \App\Models\Municipio::where('estado', true)->get();
     $roles = \App\Models\Rol::all();
     $municipiosSeleccionados = old('municipiosSeleccionados', $user->municipios->pluck('id')->toArray());
    //
    

    public function save()
    {
        // Authorize
        $this->authorize($isEdit ? 'update' : 'create', \App\Models\User::class);

        // Validación (v4 blade único permite $this->validate en @php, pero usamos manual para custom)
        $validated = $this->validate([
            'user.nombres' => 'required',
            'user.apellidos' => 'required',
            'user.cargo' => 'required',
            'user.email' => 'required|email|unique:users,email,' . ($user->id ?? 'NULL'),
            'password' => ($isEdit ? 'nullable' : 'required') . '|min:8',
            'user.rol_id' => 'required',
        ]);

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        // Sync municipios custom
        if (in_array($rolNombre, ['Técnico', 'Municipal'])) {
            if ($rolNombre === 'Municipal' && count($municipiosSeleccionados) !== 1) {
                $this->addError('municipiosSeleccionados', 'Exactamente 1 municipio.');
                return;
            }

            if ($rolNombre === 'Municipal') {
                $duplicado = \App\Models\User::where('id', '!=', $user->id)->whereHas('municipios', fn($q) => $q->where('municipio_id', $municipiosSeleccionados[0]))->whereHas('rol', fn($q) => $q->where('nombre', 'Municipal'))->exists();
                if ($duplicado) {
                    $this->addError('municipiosSeleccionados', 'Municipio ya asignado.');
                    return;
                }
            }

            $user->municipios()->sync($municipiosSeleccionados);
        } else {
            $user->municipios()->detach();
        }

        session()->flash('message', 'Usuario guardado.');
        $this->dispatch('user-saved');
    }
};
?>

<div>
    <form wire:submit="save">
        <!-- Mensajes error/flash -->
        @if (session()->has('message'))
            <div class="alert alert-success mb-4">{{ session('message') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label"><span class="label-text">Nombres</span></label>
                <input wire:model.live="user.nombres" type="text" class="input input-bordered" />
                @error('user.nombres')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Apellidos</span></label>
                <input wire:model.live="user.apellidos" type="text" class="input input-bordered" />
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Cargo</span></label>
                <input wire:model.live="user.cargo" type="text" class="input input-bordered" />
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Email</span></label>
                <input wire:model.live="user.email" type="email" class="input input-bordered" />
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Teléfono</span></label>
                <input wire:model.live="user.telefono" type="text" class="input input-bordered" />
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Rol</span></label>
                <select wire:model.live="user.rol_id" class="select select-bordered">
                    <option value="">Seleccione</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Estado</span></label>
                <input type="checkbox" wire:model.live="user.estado" class="checkbox" />
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Contraseña
                        {{ $isEdit ? '(vacío para mantener)' : '' }}</span></label>
                <input wire:model.live="password" type="password" class="input input-bordered" />
            </div>
        </div>

        @if ($user->rol_id && in_array($rolNombre, ['Técnico', 'Municipal']))
            <div class="form-control mt-6">
                <label class="label"><span class="label-text">Municipios
                        {{ $rolNombre === 'Municipal' ? '(solo 1)' : '(varios)' }}</span></label>
                <select wire:model.live="municipiosSeleccionados" {{ $rolNombre === 'Técnico' ? 'multiple' : '' }}
                    class="select select-bordered h-40">
                    @foreach ($municipios as $muni)
                        <option value="{{ $muni->id }}">{{ $muni->nombre }}</option>
                    @endforeach
                </select>
                @error('municipiosSeleccionados')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="modal-action mt-6">
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
