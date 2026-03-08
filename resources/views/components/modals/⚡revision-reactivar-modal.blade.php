<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\RevisionFinanciera;
use App\Models\TipoSolicitud;
use App\Models\Bitacora;

new class extends Component {
    public bool $abierto = false;
    public ?int $expedienteId = null;
    public ?int $tipoSolicitudId = null;

    public string $faseNombre = '';
    public string $motivoRechazo = '';
    public string $observaciones = '';

    #[On('abrir-reactivar-fase')]
    public function abrir(int $expedienteId, int $tipoSolicitudId)
    {
        $this->expedienteId = $expedienteId;
        $this->tipoSolicitudId = $tipoSolicitudId;
        $this->observaciones = '';

        $fase = TipoSolicitud::find($tipoSolicitudId);
        $this->faseNombre = $fase ? $fase->nombre : 'N/A';

        // Obtener el motivo del rechazo (última revisión rechazada de esta fase)
        $ultimaRechazada = RevisionFinanciera::where('expediente_id', $expedienteId)->where('tipo_solicitud_id', $tipoSolicitudId)->where('accion', RevisionFinanciera::ACCION_RECHAZAR)->orderBy('id', 'desc')->first();

        $this->motivoRechazo = $ultimaRechazada?->observaciones ?? 'Sin observaciones';

        $this->abierto = true;
    }

    public function reactivar()
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Solo el administrador puede reactivar fases rechazadas.');
            return;
        }

        $this->validate(
            [
                'observaciones' => 'required|string|min:10|max:2000',
            ],
            [
                'observaciones.required' => 'Debe indicar el motivo de la reactivación.',
                'observaciones.min' => 'La observación debe tener al menos 10 caracteres.',
                'observaciones.max' => 'La observación no puede exceder 2000 caracteres.',
            ],
        );

        $expediente = Expediente::findOrFail($this->expedienteId);

        // Contar revisiones existentes para esta fase
        $numRevision = RevisionFinanciera::where('expediente_id', $this->expedienteId)->where('tipo_solicitud_id', $this->tipoSolicitudId)->count() + 1;

        // Crear nueva revisión de reactivación
        RevisionFinanciera::create([
            'expediente_id' => $this->expedienteId,
            'tipo_solicitud_id' => $this->tipoSolicitudId,
            'numero_revision' => $numRevision,
            'revisor_id' => $user->id,
            'estado' => RevisionFinanciera::ESTADO_INCOMPLETO,
            'accion' => RevisionFinanciera::ACCION_REACTIVAR,
            'observaciones' => $this->observaciones,
            'fecha_revision' => now(),
        ]);

        // Registrar en bitácora
        Bitacora::registrarRevision("Fase '{$this->faseNombre}' reactivada por administrador en Expediente {$expediente->codigo_snip}", $expediente->id);

        $this->abierto = false;
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Fase '{$this->faseNombre}' reactivada correctamente.");
        $this->dispatch('fase-reactivada');
    }

    public function cancelar()
    {
        $this->abierto = false;
        $this->reset(['observaciones', 'expedienteId', 'tipoSolicitudId']);
    }
};
?>

<div x-on:keydown.escape.window="if ($wire.abierto) $wire.cancelar()">
    <div class="modal" :class="{ 'modal-open': $wire.abierto }">
        <div class="modal-box max-w-lg">
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-info/10 text-info rounded-btn p-2">
                    <x-heroicon-o-arrow-path class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold">Reactivar Fase Rechazada</h3>
                    <p class="text-sm text-base-content/60">{{ $faseNombre }}</p>
                </div>
            </div>

            <div class="divider my-2"></div>

            {{-- Motivo del rechazo original --}}
            <div class="bg-error/5 border border-error/20 rounded-lg p-3 mb-4">
                <p class="text-xs font-semibold text-error mb-1">Motivo del rechazo:</p>
                <p class="text-sm">{{ $motivoRechazo }}</p>
            </div>

            {{-- Explicación --}}
            <div role="alert" class="alert alert-info mb-4">
                <x-heroicon-o-information-circle class="stroke-current shrink-0 h-5 w-5" />
                <p class="text-sm">
                    Al reactivar esta fase, se creará un nuevo registro de revisión y el revisor financiero podrá
                    continuar agregando revisiones desde esta fase en adelante.
                </p>
            </div>

            <form wire:submit="reactivar" class="space-y-4">
                {{-- Observaciones --}}
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend">Motivo de reactivación <span class="text-error">*</span></legend>
                    <textarea wire:model="observaciones" rows="4"
                        class="textarea w-full @error('observaciones') textarea-error @enderror"
                        placeholder="Explique por qué se reactiva esta fase (ej: documentación corregida, error administrativo, etc.)"
                        maxlength="2000"></textarea>
                    @error('observaciones')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                    <p class="label text-base-content/50">{{ strlen($observaciones) }}/2000</p>
                </fieldset>

                {{-- Botones --}}
                <div class="modal-action">
                    <button type="button" wire:click="cancelar" class="btn btn-ghost">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-info gap-2" wire:loading.attr="disabled">
                        <span wire:loading wire:target="reactivar" class="loading loading-spinner loading-sm"></span>
                        <x-heroicon-o-arrow-path class="w-5 h-5" wire:loading.remove wire:target="reactivar" />
                        Reactivar Fase
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop" wire:click="cancelar"></div>
    </div>
</div>
