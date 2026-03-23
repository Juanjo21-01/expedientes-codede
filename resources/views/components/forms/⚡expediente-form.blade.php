<?php

use Livewire\Component;
use App\Actions\Expedientes\GuardarExpedienteAction;
use App\Models\Expediente;
use App\Models\Municipio;
use App\Models\User;
use App\Models\Role;
use Livewire\Attributes\Computed;

new class extends Component {
    // Props
    public $expedienteId = null;
    public bool $modoEdicion = false;

    // Campos del formulario
    public string $codigo_snip = '';
    public string $nombre_proyecto = '';
    public $municipio_id = '';
    public $responsable_id = '';
    public string $tipo_asignacion = '';
    public string $fecha_recibido = '';
    public $monto_contrato = '';
    public $aporte_municipalidad = '';
    public string $observaciones = '';

    public function mount($expedienteId = null)
    {
        $this->expedienteId = $expedienteId;

        if ($this->expedienteId) {
            $this->modoEdicion = true;
            $this->cargarExpediente();
        } else {
            // Valores por defecto para creación
            $this->fecha_recibido = now()->format('Y-m-d');

            $user = auth()->user();
            // Si es Técnico, auto-asignar como responsable
            if ($user->isTecnico()) {
                $this->responsable_id = $user->id;
                // Si solo tiene un municipio asignado, auto-seleccionar
                $municipios = $user->municipios;
                if ($municipios->count() === 1) {
                    $this->municipio_id = $municipios->first()->id;
                }
            }
        }
    }

    public function cargarExpediente()
    {
        $expediente = Expediente::find($this->expedienteId);
        if (!$expediente) {
            return;
        }

        $this->codigo_snip = $expediente->codigo_snip;
        $this->nombre_proyecto = $expediente->nombre_proyecto;
        $this->municipio_id = $expediente->municipio_id;
        $this->responsable_id = $expediente->responsable_id;
        $this->tipo_asignacion = $expediente->tipo_asignacion;
        $this->fecha_recibido = $expediente->fecha_recibido->format('Y-m-d');
        $this->monto_contrato = $expediente->monto_contrato ?? '';
        $this->aporte_municipalidad = $expediente->aporte_municipalidad ?? '';
        $this->observaciones = $expediente->observaciones ?? '';
    }

    // Municipios disponibles según rol
    #[Computed]
    public function municipiosDisponibles()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return Municipio::activos()->ordenados()->get();
        }

        return $user->municipios()->ordenados()->get();
    }

    // Responsables disponibles (técnicos del municipio seleccionado)
    #[Computed]
    public function responsablesDisponibles()
    {
        $user = auth()->user();

        // Admin puede asignar cualquier técnico
        if ($user->isAdmin()) {
            if ($this->municipio_id) {
                return User::activos()->tecnicos()->whereHas('municipios', fn($q) => $q->where('municipios.id', $this->municipio_id))->orderBy('nombres')->get();
            }
            return User::activos()->tecnicos()->orderBy('nombres')->get();
        }

        // Técnico se auto-asigna
        return collect([$user]);
    }

    // Cuando cambia el municipio, actualizar responsables
    public function updatedMunicipioId()
    {
        unset($this->responsablesDisponibles);

        $user = auth()->user();
        // Si es admin y cambió de municipio, verificar si el responsable actual sigue siendo válido
        if ($user->isAdmin() && $this->responsable_id) {
            $responsableValido = $this->responsablesDisponibles->contains('id', (int) $this->responsable_id);

            if (!$responsableValido) {
                $this->responsable_id = '';
            }
        }
    }

    public function guardar()
    {
        $action = app(GuardarExpedienteAction::class);
        $validated = $action->validar(
            [
                'codigo_snip' => $this->codigo_snip,
                'nombre_proyecto' => $this->nombre_proyecto,
                'municipio_id' => $this->municipio_id,
                'responsable_id' => $this->responsable_id,
                'tipo_asignacion' => $this->tipo_asignacion,
                'fecha_recibido' => $this->fecha_recibido,
                'monto_contrato' => $this->monto_contrato,
                'aporte_municipalidad' => $this->aporte_municipalidad,
                'observaciones' => $this->observaciones,
            ],
            $this->modoEdicion,
            $this->expedienteId,
        );

        $expediente = $action->ejecutar($validated, $this->modoEdicion, $this->expedienteId);

        if (!$expediente) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Expediente no encontrado.');
            return;
        }

        if ($this->modoEdicion) {
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Expediente '{$expediente->codigo_snip}' actualizado correctamente.");
            $this->dispatch('expediente-guardado');
        } else {
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Expediente '{$expediente->codigo_snip}' creado correctamente.");
        }

        return $this->redirect(route('expedientes.show', $expediente->id), navigate: true);
    }

    public function cancelar()
    {
        return $this->redirect(route('expedientes.index'), navigate: true);
    }
};
?>

<div>
    <form wire:submit="guardar" class="space-y-6">
        {{-- Sección: Información General --}}
        <div class="card rounded-box border border-base-content/10 bg-base-100 shadow-sm">
            <div class="card-body p-4 sm:p-6">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                    Información del Proyecto
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Código SNIP --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Código SNIP <span class="text-error">*</span></legend>
                        <input type="text" wire:model="codigo_snip"
                            class="input w-full border-base-content/20 @error('codigo_snip') input-error @enderror"
                            placeholder="Ej: 123456" />
                        @error('codigo_snip')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Fecha Recibido --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Fecha de Recibido <span class="text-error">*</span></legend>
                        <input type="date" wire:model="fecha_recibido"
                            class="input w-full border-base-content/20 @error('fecha_recibido') input-error @enderror" />
                        @error('fecha_recibido')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Nombre del Proyecto --}}
                    <fieldset class="fieldset w-full md:col-span-2">
                        <legend class="fieldset-legend">Nombre del Proyecto <span class="text-error">*</span></legend>
                        <input type="text" wire:model="nombre_proyecto"
                            class="input w-full border-base-content/20 @error('nombre_proyecto') input-error @enderror"
                            placeholder="Nombre completo del proyecto" />
                        @error('nombre_proyecto')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            </div>

        </div>

        <div class="divider my-1"></div>

        {{-- Sección: Clasificación --}}
        <div class="card rounded-box border border-base-content/10 bg-base-100 shadow-sm">
            <div class="card-body p-4 sm:p-6">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-tag class="w-5 h-5 text-primary" />
                    Clasificación
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Municipio --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Municipio <span class="text-error">*</span></legend>
                        <select wire:model.live="municipio_id"
                            class="select w-full border-base-content/20 @error('municipio_id') select-error @enderror"
                            {{ !auth()->user()->isAdmin() && $this->municipiosDisponibles->count() === 1 ? 'disabled' : '' }}>
                            <option value="" selected disabled>Seleccionar municipio...</option>
                            @foreach ($this->municipiosDisponibles as $mun)
                                <option value="{{ $mun->id }}">{{ $mun->nombre }} - {{ $mun->departamento }}
                                </option>
                            @endforeach
                        </select>
                        @error('municipio_id')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Ordinario/Extraordinario --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Tipo de Asignación <span class="text-error">*</span></legend>
                        <select wire:model="tipo_asignacion"
                            class="select w-full border-base-content/20 @error('tipo_asignacion') select-error @enderror">
                            <option value="" selected disabled>Seleccionar asignación...</option>
                            @foreach (Expediente::getTipos() as $tipo)
                                <option value="{{ $tipo }}">{{ ucfirst(strtolower($tipo)) }}</option>
                            @endforeach
                        </select>
                        @error('tipo_asignacion')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="divider my-1"></div>

        {{-- Sección: Responsable (Admin puede elegir, Técnico se auto-asigna) --}}
        <div class="card rounded-box border border-base-content/10 bg-base-100 shadow-sm">
            <div class="card-body p-4 sm:p-6">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary" />
                    Responsable
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Responsable --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Técnico Responsable <span class="text-error">*</span></legend>
                        @if (auth()->user()->isAdmin())
                            <select wire:model="responsable_id"
                                class="select w-full border-base-content/20 @error('responsable_id') select-error @enderror">
                                <option value="" selected disabled>Seleccionar técnico...</option>
                                @foreach ($this->responsablesDisponibles as $resp)
                                    <option value="{{ $resp->id }}">{{ $resp->nombre_completo }}</option>
                                @endforeach
                            </select>
                            @if ($this->municipio_id && $this->responsablesDisponibles->isEmpty())
                                <p class="label text-warning">No hay técnicos asignados a este municipio</p>
                            @endif
                        @else
                            <input type="text" value="{{ auth()->user()->nombre_completo }}"
                                class="input w-full border-base-content/20 bg-base-200" disabled />
                        @endif
                        @error('responsable_id')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="divider my-1"></div>

        {{-- Sección: Información Financiera (opcional) --}}
        <div class="card rounded-box border border-base-content/10 bg-base-100 shadow-sm">
            <div class="card-body p-4 sm:p-6">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-primary" />
                    Información Financiera
                    <span class="badge badge-sm badge-ghost">Opcional</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Monto del Contrato --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Monto del Contrato (Q)</legend>
                        <input type="number" wire:model="monto_contrato" step="0.01" min="0"
                            class="input w-full border-base-content/20 @error('monto_contrato') input-error @enderror"
                            placeholder="0.00" />
                        @error('monto_contrato')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Aporte de la Municipalidad --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Aporte de la Municipalidad (Q)</legend>
                        <input type="number" wire:model="aporte_municipalidad" step="0.01" min="0"
                            class="input w-full border-base-content/20 @error('aporte_municipalidad') input-error @enderror"
                            placeholder="0.00" />
                        @error('aporte_municipalidad')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="divider my-1"></div>

        {{-- Observaciones --}}
        <div class="card rounded-box border border-base-content/10 bg-base-100 shadow-sm">
            <div class="card-body">
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend">Observaciones</legend>
                    <textarea wire:model="observaciones"
                        class="textarea w-full border-base-content/20 @error('observaciones') textarea-error @enderror"
                        placeholder="Observaciones adicionales..." rows="3" maxlength="1000"></textarea>
                    @error('observaciones')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                    <p class="label text-base-content/50">{{ strlen($observaciones) }}/1000 caracteres</p>
                </fieldset>
            </div>
        </div>

        {{-- Botones --}}
        <div class="divider my-1"></div>
        <div class="flex justify-end gap-3">
            <button type="button" wire:click="cancelar" class="btn btn-ghost">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="guardar">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                </span>
                <span wire:loading wire:target="guardar" class="loading loading-spinner loading-sm"></span>
                {{ $modoEdicion ? 'Guardar Cambios' : 'Crear Expediente' }}
            </button>
        </div>
    </form>
</div>
