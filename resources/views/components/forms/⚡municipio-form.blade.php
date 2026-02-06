<?php

use Livewire\Component;
use App\Models\Municipio;

new class extends Component {
    // ID del municipio
    public $municipioId = null;

    // Campos de solo lectura (informativos)
    public $nombre = '';
    public $departamento = '';

    // Campos editables
    public $contacto_nombre = '';
    public $contacto_email = '';
    public $contacto_telefono = '';
    public $observaciones = '';

    // Mount: cargar municipio
    public function mount($municipioId = null)
    {
        $this->municipioId = $municipioId;

        if ($this->municipioId) {
            $this->cargarMunicipio($this->municipioId);
        }
    }

    // Cargar datos del municipio
    public function cargarMunicipio($id)
    {
        $municipio = Municipio::find($id);

        if ($municipio) {
            $this->nombre = $municipio->nombre;
            $this->departamento = $municipio->departamento;
            $this->contacto_nombre = $municipio->contacto_nombre ?? '';
            $this->contacto_email = $municipio->contacto_email ?? '';
            $this->contacto_telefono = $municipio->contacto_telefono ?? '';
            $this->observaciones = $municipio->observaciones ?? '';
        }
    }

    // Guardar
    public function guardar()
    {
        $this->validate(
            [
                'contacto_nombre' => 'nullable|string|max:100',
                'contacto_email' => 'nullable|email|max:255',
                'contacto_telefono' => 'nullable|string|max:8',
                'observaciones' => 'nullable|string|max:1000',
            ],
            [
                'contacto_email.email' => 'El correo de contacto debe ser válido.',
                'contacto_nombre.max' => 'El nombre de contacto no debe exceder 100 caracteres.',
                'contacto_telefono.max' => 'El teléfono debe tener máximo 8 dígitos.',
                'observaciones.max' => 'Las observaciones no deben exceder 1000 caracteres.',
            ],
        );

        $municipio = Municipio::find($this->municipioId);

        if (!$municipio) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Municipio no encontrado.');
            $this->dispatch('cerrar-modal-municipio');
            return;
        }

        $municipio->update([
            'contacto_nombre' => $this->contacto_nombre ?: null,
            'contacto_email' => $this->contacto_email ?: null,
            'contacto_telefono' => $this->contacto_telefono ?: null,
            'observaciones' => $this->observaciones ?: null,
        ]);

        $this->dispatch('municipio-guardado');
        $this->dispatch('cerrar-modal-municipio');
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Municipio '{$municipio->nombre}' actualizado correctamente.");
    }

    // Cancelar
    public function cancelar()
    {
        $this->dispatch('cerrar-modal-municipio');
    }
};
?>

<div class="py-4">
    <!-- Info del municipio (solo lectura) -->
    <div class="bg-base-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 text-primary rounded-btn p-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 21h19.5M3.75 3v18m16.5-18v18M5.25 3h13.5M5.25 21h13.5M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div>
                <div class="font-bold text-lg">{{ $nombre }}</div>
                <div class="text-sm opacity-60">{{ $departamento }}</div>
            </div>
        </div>
    </div>

    <p class="text-sm text-base-content/60 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-4 h-4 inline-block mr-1">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        Edita la información de contacto y observaciones del municipio.
    </p>

    <!-- Formulario -->
    <form wire:submit="guardar" class="space-y-4">
        <!-- Nombre de contacto -->
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-medium">Nombre de Contacto</span>
            </label>
            <input type="text" wire:model="contacto_nombre"
                class="input input-bordered w-full focus:input-primary transition-colors @error('contacto_nombre') input-error @enderror"
                placeholder="Nombre del contacto municipal" maxlength="100" />
            @error('contacto_nombre')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <!-- Email de contacto -->
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-medium">Correo Electrónico</span>
            </label>
            <input type="email" wire:model="contacto_email"
                class="input input-bordered w-full focus:input-primary transition-colors @error('contacto_email') input-error @enderror"
                placeholder="correo@ejemplo.com" />
            @error('contacto_email')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <!-- Teléfono de contacto -->
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-medium">Teléfono</span>
            </label>
            <input type="text" wire:model="contacto_telefono"
                class="input input-bordered w-full focus:input-primary transition-colors @error('contacto_telefono') input-error @enderror"
                placeholder="12345678" maxlength="8" />
            @error('contacto_telefono')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <!-- Observaciones -->
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-medium">Observaciones</span>
            </label>
            <textarea wire:model="observaciones"
                class="textarea textarea-bordered w-full focus:textarea-primary transition-colors @error('observaciones') textarea-error @enderror"
                placeholder="Observaciones adicionales sobre el municipio..." rows="3" maxlength="1000"></textarea>
            @error('observaciones')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
            <label class="label">
                <span class="label-text-alt text-base-content/50">{{ strlen($observaciones) }}/1000 caracteres</span>
            </label>
        </div>

        <!-- Botones -->
        <div class="divider my-2"></div>
        <div class="flex justify-end gap-2">
            <button type="button" wire:click="cancelar" class="btn btn-ghost">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="guardar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
