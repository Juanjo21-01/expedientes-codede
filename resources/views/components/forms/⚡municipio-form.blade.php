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
    public $contactoNombre = '';
    public $contactoEmail = '';
    public $contactoTelefono = '';
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
            $this->contactoNombre = $municipio->contacto_nombre ?? '';
            $this->contactoEmail = $municipio->contacto_email ?? '';
            $this->contactoTelefono = $municipio->contacto_telefono ?? '';
            $this->observaciones = $municipio->observaciones ?? '';
        }
    }

    // Guardar
    public function guardar()
    {
        $this->validate(
            [
                'contactoNombre' => 'nullable|string|max:100',
                'contactoEmail' => 'nullable|email|max:255',
                'contactoTelefono' => 'nullable|string|max:8',
                'observaciones' => 'nullable|string|max:1000',
            ],
            [
                'contactoEmail.email' => 'El correo de contacto debe ser válido.',
                'contactoNombre.max' => 'El nombre de contacto no debe exceder 100 caracteres.',
                'contactoTelefono.max' => 'El teléfono debe tener máximo 8 dígitos.',
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
            'contacto_nombre' => $this->contactoNombre ?: null,
            'contacto_email' => $this->contactoEmail ?: null,
            'contacto_telefono' => $this->contactoTelefono ?: null,
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
                <x-heroicon-o-building-office-2 class="w-5 h-5" />
            </div>
            <div>
                <div class="font-bold text-lg">{{ $nombre }}</div>
                <div class="text-sm opacity-60">{{ $departamento }}</div>
            </div>
        </div>
    </div>

    <p class="text-sm text-base-content/60 mb-4">
        <x-heroicon-o-information-circle class="w-4 h-4 inline-block mr-1" />
        Edita la información de contacto y observaciones del municipio.
    </p>

    <!-- Formulario -->
    <form wire:submit="guardar" class="space-y-4">
        <!-- Nombre de contacto -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Nombre de Contacto</legend>
            <input type="text" wire:model="contactoNombre"
                class="input w-full @error('contactoNombre') input-error @enderror"
                placeholder="Nombre del contacto municipal" maxlength="100" />
            @error('contactoNombre')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Email de contacto -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Correo Electrónico</legend>
            <label class="input w-full @error('contactoEmail') input-error @enderror">
                <x-heroicon-o-envelope class="h-[1em] opacity-50" />
                <input type="email" wire:model="contactoEmail" class="grow" placeholder="correo@ejemplo.com" />
            </label>
            @error('contactoEmail')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Teléfono de contacto -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Teléfono</legend>
            <label class="input w-full @error('contactoTelefono') input-error @enderror">
                <x-heroicon-o-phone class="h-[1em] opacity-50" />
                <input type="text" wire:model="contactoTelefono" class="grow" placeholder="12345678"
                    maxlength="8" />
            </label>
            @error('contactoTelefono')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Observaciones -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Observaciones</legend>
            <textarea wire:model="observaciones" class="textarea w-full h-24 @error('observaciones') textarea-error @enderror"
                placeholder="Observaciones adicionales sobre el municipio..." rows="3" maxlength="1000"></textarea>
            @error('observaciones')
                <p class="label text-error">{{ $message }}</p>
            @enderror
            <p class="label text-base-content/50">{{ strlen($observaciones) }}/1000 caracteres</p>
        </fieldset>

        <!-- Botones -->
        <div class="divider my-2"></div>
        <div class="flex justify-end gap-2">
            <button type="button" wire:click="cancelar" class="btn btn-ghost">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="guardar">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                </span>
                <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
