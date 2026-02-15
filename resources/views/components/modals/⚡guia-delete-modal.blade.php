<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;
use App\Models\Guia;

new class extends Component {
    public bool $mostrar = false;
    public ?int $guiaId = null;
    public string $password = '';
    public string $tituloGuia = '';
    public string $categoriaGuia = '';
    public int $versionGuia = 0;

    #[On('abrir-delete-modal')]
    public function abrir(int $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $this->authorize('delete', $guia);

        $this->guiaId = $guiaId;
        $this->tituloGuia = $guia->titulo;
        $this->categoriaGuia = $guia->categoria;
        $this->versionGuia = $guia->version;
        $this->password = '';
        $this->resetValidation();
        $this->mostrar = true;
    }

    public function cerrar()
    {
        $this->mostrar = false;
        $this->reset(['guiaId', 'password', 'tituloGuia', 'categoriaGuia', 'versionGuia']);
        $this->resetValidation();
    }

    public function eliminar()
    {
        $this->validate(
            [
                'password' => 'required|string',
            ],
            [
                'password.required' => 'Debe ingresar su contraseña para confirmar.',
            ],
        );

        // Verificar contraseña del usuario autenticado
        if (!Hash::check($this->password, auth()->user()->password)) {
            $this->addError('password', 'La contraseña es incorrecta.');
            return;
        }

        $guia = Guia::findOrFail($this->guiaId);
        $this->authorize('delete', $guia);

        $categoria = $guia->categoria;

        // Eliminar archivo físico
        $guia->eliminarArchivo();

        // Eliminar registro
        $guia->delete();

        // Reordenar versiones de la categoría para mantener secuencia continua
        Guia::reordenarVersiones($categoria);

        $this->cerrar();
        $this->dispatch('guia-eliminada');
        $this->redirectRoute('admin.guias.index', navigate: true);
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Guía eliminada exitosamente.');
    }
};
?>

<div>
    @if ($mostrar)
        <div class="modal modal-open">
            <div class="modal-box max-w-md" wire:click.stop>
                {{-- Encabezado --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-error/10 text-error rounded-lg w-10 h-10 flex items-center justify-center">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Eliminar Guía</h3>
                        <p class="text-sm text-base-content/60">Esta acción no se puede deshacer</p>
                    </div>
                </div>

                {{-- Info de la guía --}}
                <div class="bg-base-200 rounded-lg p-4 space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Título:</span>
                        <span class="font-medium">{{ $tituloGuia }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Categoría:</span>
                        <span class="font-medium">{{ $categoriaGuia }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Versión:</span>
                        <span class="font-mono">v{{ $versionGuia }}</span>
                    </div>
                </div>

                <div role="alert" class="alert alert-warning mb-4">
                    <x-heroicon-o-exclamation-triangle class="stroke-current shrink-0 h-5 w-5" />
                    <span class="text-xs">Se eliminará el registro y el archivo PDF del servidor permanentemente.</span>
                </div>

                {{-- Campo de contraseña --}}
                <form wire:submit.prevent="eliminar">
                    <fieldset class="fieldset w-full mb-4">
                        <legend class="fieldset-legend">Ingrese su contraseña para confirmar</legend>
                        <input type="password" wire:model="password"
                            class="input w-full @error('password') input-error @enderror"
                            placeholder="Su contraseña actual" autocomplete="off" />
                        @error('password')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Botones --}}
                    <div class="modal-action">
                        <button type="button" wire:click="cerrar" class="btn btn-ghost">Cancelar</button>
                        <button type="submit" class="btn btn-error gap-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="eliminar">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </span>
                            <span wire:loading wire:target="eliminar" class="loading loading-spinner loading-sm"></span>
                            Eliminar Guía
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="cerrar"></div>
        </div>
    @endif
</div>
