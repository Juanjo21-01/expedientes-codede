<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Expediente;

new #[Title('- Revisión Financiera')] class extends Component {
    public Expediente $expediente;

    public function mount(Expediente $expediente)
    {
        $this->expediente = $expediente->load(['municipio', 'responsable', 'tipoSolicitud', 'revisionesFinancieras.revisor']);
    }

    // Abrir modal de notificación
    public function notificar()
    {
        $this->dispatch('abrir-notificacion-modal', expedienteId: $this->expediente->id);
    }
};
?>

<div>
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm mb-6">
        <ul>
            <li>
                <a href="{{ route('expedientes.index') }}" wire:navigate
                    class="font-medium text-base-content/60 hover:text-primary">
                    <x-heroicon-o-folder class="w-4 h-4 mr-1" />
                    Expedientes
                </a>
            </li>
            <li>
                <a href="{{ route('expedientes.show', $expediente->id) }}" wire:navigate
                    class="font-medium text-base-content/60 hover:text-primary">
                    <span class="font-mono">{{ $expediente->codigo_snip }}</span>
                </a>
            </li>
            <li><span class="font-medium text-primary">Revisión Financiera</span></li>
        </ul>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="avatar placeholder">
                <div class="bg-accent/10 text-accent rounded-lg w-12 h-12 flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Revisión Financiera</h1>
                <p class="text-base-content/60 text-sm">
                    <span class="font-mono">{{ $expediente->codigo_snip }}</span> ·
                    {{ $expediente->nombre_proyecto }}
                </p>
            </div>
        </div>
        <button wire:click="notificar" class="btn btn-info btn-sm gap-2">
            <x-heroicon-o-envelope class="w-4 h-4" />
            Notificar
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda: Info del expediente + Historial --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Resumen del Expediente --}}
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm">Resumen del Expediente</h3>
                    <div class="divider my-1"></div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Estado</span>
                            <span class="badge {{ $expediente->estado_badge_class }}">{{ $expediente->estado }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Municipio</span>
                            <span class="font-medium">{{ $expediente->municipio->nombre }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Tipo Solicitud</span>
                            <span>{{ $expediente->tipoSolicitud->nombre }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Tipo</span>
                            <span>{{ ucfirst(strtolower($expediente->ordinario_extraordinario)) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Monto</span>
                            <span class="font-bold">{{ $expediente->monto_formateado }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Adjudicatario</span>
                            <span>{{ $expediente->adjudicatario ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Responsable</span>
                            <span>{{ $expediente->responsable->nombre_completo }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Recibido</span>
                            <span>{{ $expediente->fecha_recibido->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Historial de Revisiones --}}
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        Historial de Revisiones
                        @if ($expediente->revisionesFinancieras->isNotEmpty())
                            <span
                                class="badge badge-sm badge-primary">{{ $expediente->revisionesFinancieras->count() }}</span>
                        @endif
                    </h3>
                    <div class="divider my-1"></div>

                    @if ($expediente->revisionesFinancieras->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($expediente->revisionesFinancieras->sortByDesc('fecha_revision') as $revision)
                                <div class="border border-base-300 rounded-lg p-3 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="badge badge-sm {{ $revision->estado_badge_class }}">{{ $revision->estado }}</span>
                                        <span
                                            class="text-xs text-base-content/50">{{ $revision->fecha_revision->format('d/m/Y') }}</span>
                                    </div>
                                    @if ($revision->tieneAccion())
                                        <div class="mt-1">
                                            <span
                                                class="badge badge-sm {{ $revision->accion_badge_class }}">{{ $revision->accion_texto }}</span>
                                        </div>
                                    @endif
                                    <div class="text-xs text-base-content/60 mt-1">
                                        {{ $revision->revisor->nombre_completo }}
                                    </div>
                                    @if ($revision->observaciones)
                                        <div class="mt-1 text-xs bg-base-200 rounded p-2">
                                            {{ Str::limit($revision->observaciones, 120) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-base-content/40 text-center py-4">Sin revisiones previas</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: Formulario de Revisión --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-base gap-2">
                        <x-heroicon-o-pencil-square class="w-5 h-5 text-accent" />
                        Registrar Nueva Revisión
                    </h3>
                    <div class="divider my-1"></div>
                    <livewire:forms.revision-financiera-form :expedienteId="$expediente->id" />
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de notificación --}}
    <livewire:modals.notificacion-modal />
</div>
