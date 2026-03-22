<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\RevisionFinanciera;

new #[Title('- Revisión Financiera')] class extends Component {
    public Expediente $expediente;

    public function mount(Expediente $expediente)
    {
        $this->expediente = $expediente->load(['municipio', 'responsable', 'revisionesFinancieras.revisor', 'revisionesFinancieras.tipoSolicitud']);
    }

    #[On('fase-reactivada')]
    public function refrescar()
    {
        $this->expediente = $this->expediente->fresh(['municipio', 'responsable', 'revisionesFinancieras.revisor', 'revisionesFinancieras.tipoSolicitud']);
    }
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm">
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
    <div class="card border border-accent/20 bg-linear-to-br from-accent/10 via-base-100 to-base-100 shadow-sm">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="avatar placeholder shrink-0">
                        <div class="bg-accent/10 text-accent rounded-lg w-12 h-12 flex items-center justify-center">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl font-bold">Revisión Financiera</h1>
                            <span
                                class="badge badge-soft {{ $expediente->estado_badge_class }}">{{ $expediente->estado }}</span>
                        </div>
                        <p class="text-base-content/60 text-sm mt-0.5">
                            <span class="font-mono font-semibold">{{ $expediente->codigo_snip }}</span> ·
                            {{ Str::limit($expediente->nombre_proyecto, 60) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('expedientes.show', $expediente->id) }}" wire:navigate
                        class="btn btn-outline btn-sm gap-2">
                        <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                        Volver
                    </a>
                    <button @click="$dispatch('abrir-notificacion-modal', { expedienteId: {{ $expediente->id }} })"
                        class="btn btn-info btn-sm gap-2">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                        Notificar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda: Info del expediente + Historial --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Resumen del Expediente --}}
            <div class="card border border-base-content/10 bg-base-100 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-4 h-4 text-accent" />
                        Resumen del Expediente
                    </h3>
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
                            <span class="text-base-content/60">Tipo</span>
                            <span>{{ ucfirst(strtolower($expediente->tipo_asignacion)) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Monto Contrato</span>
                            <span class="font-bold">{{ $expediente->monto_formateado }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Aporte Municipal</span>
                            <span>{{ $expediente->aporte_municipalidad_formateado }}</span>
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
            <div class="card border border-base-content/10 bg-base-100 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <x-heroicon-o-clock class="w-4 h-4 text-accent" />
                        Historial de Revisiones
                        @if ($expediente->revisionesFinancieras->isNotEmpty())
                            <span
                                class="badge badge-sm badge-primary">{{ $expediente->revisionesFinancieras->count() }}</span>
                        @endif
                    </h3>
                    <div class="divider my-1"></div>

                    @if ($expediente->revisionesFinancieras->isNotEmpty())
                        <ul class="timeline timeline-vertical timeline-compact timeline-snap-icon">
                            @foreach ($expediente->revisionesFinancieras->sortByDesc('fecha_revision') as $revision)
                                @php
                                    $iconColor = match (true) {
                                        $revision->accion === RevisionFinanciera::ACCION_APROBAR => 'text-success',
                                        $revision->accion === RevisionFinanciera::ACCION_RECHAZAR => 'text-error',
                                        $revision->accion === RevisionFinanciera::ACCION_REACTIVAR => 'text-info',
                                        $revision->accion === RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES
                                            => 'text-warning',
                                        default => 'text-base-content/30',
                                    };
                                @endphp
                                <li>
                                    @unless ($loop->first)
                                        <hr class="{{ $iconColor }}" />
                                    @endunless
                                    <div class="timeline-middle">
                                        @if ($revision->accion === RevisionFinanciera::ACCION_APROBAR)
                                            <x-heroicon-s-check-circle class="w-5 h-5 {{ $iconColor }}" />
                                        @elseif ($revision->accion === RevisionFinanciera::ACCION_RECHAZAR)
                                            <x-heroicon-s-x-circle class="w-5 h-5 {{ $iconColor }}" />
                                        @elseif ($revision->accion === RevisionFinanciera::ACCION_REACTIVAR)
                                            <x-heroicon-s-arrow-path class="w-5 h-5 {{ $iconColor }}" />
                                        @elseif ($revision->accion === RevisionFinanciera::ACCION_SOLICITAR_CORRECCIONES)
                                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 {{ $iconColor }}" />
                                        @else
                                            <x-heroicon-o-ellipsis-horizontal-circle
                                                class="w-5 h-5 {{ $iconColor }}" />
                                        @endif
                                    </div>
                                    <div class="timeline-end mb-6 w-full">
                                        {{-- Fecha y fase --}}
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span
                                                class="text-xs font-medium text-base-content/50">{{ $revision->fecha_revision->format('d/m/Y') }}</span>
                                            @if ($revision->tipoSolicitud)
                                                <span
                                                    class="badge badge-xs badge-outline">{{ $revision->tipoSolicitud->nombre }}</span>
                                            @endif
                                        </div>
                                        {{-- Estado y acción --}}
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span
                                                class="badge badge-sm badge-soft {{ $revision->estado_badge_class }}">{{ $revision->estado }}</span>
                                            @if ($revision->tieneAccion())
                                                <span
                                                    class="badge badge-sm badge-soft {{ $revision->accion_badge_class }}">{{ $revision->accion_texto }}</span>
                                            @endif
                                        </div>
                                        {{-- Monto --}}
                                        @if ($revision->monto_aprobado)
                                            <p class="text-xs mt-1.5">
                                                <span class="text-base-content/50">Monto:</span>
                                                <span
                                                    class="font-bold text-success">{{ $revision->monto_formateado }}</span>
                                            </p>
                                        @endif
                                        {{-- Revisor --}}
                                        <p class="text-xs text-base-content/50 mt-1">
                                            <x-heroicon-o-user class="w-3 h-3 inline -mt-0.5" />
                                            {{ $revision->revisor->nombre_completo }}
                                        </p>
                                        {{-- Observaciones --}}
                                        @if ($revision->observaciones)
                                            <div
                                                class="mt-1.5 text-xs bg-base-200/60 rounded-lg px-2.5 py-2 text-base-content/70 leading-relaxed">
                                                {{ Str::limit($revision->observaciones, 120) }}
                                            </div>
                                        @endif
                                        {{-- Botón reactivar --}}
                                        @if (auth()->user()->isAdmin() &&
                                                $revision->accion === RevisionFinanciera::ACCION_RECHAZAR &&
                                                $revision->id ===
                                                    $expediente->revisionesFinancieras->where('tipo_solicitud_id', $revision->tipo_solicitud_id)->sortByDesc('id')->first()?->id)
                                            <button
                                                @click="$dispatch('abrir-reactivar-fase', { expedienteId: {{ $expediente->id }}, tipoSolicitudId: {{ $revision->tipo_solicitud_id }} })"
                                                class="btn btn-info btn-xs gap-1 mt-2 w-full">
                                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                                Reactivar fase
                                            </button>
                                        @endif
                                    </div>
                                    @unless ($loop->last)
                                        <hr />
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-6">
                            <div class="bg-base-200 rounded-full p-3 w-fit mx-auto mb-2">
                                <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-base-content/20" />
                            </div>
                            <p class="text-sm text-base-content/40">Sin revisiones previas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: Formulario de Revisión --}}
        <div class="lg:col-span-2">
            <div class="card border border-base-content/10 bg-base-100 shadow-sm">
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

    {{-- Modal de información inicial (si falta monto_contrato) --}}
    <livewire:modals.expediente-info-revision-modal :expedienteId="$expediente->id" />

    {{-- Modal de reactivación de fase rechazada (solo admin) --}}
    @if (auth()->user()->isAdmin())
        <livewire:modals.revision-reactivar-modal />
    @endif

    {{-- Modal de notificación --}}
    <livewire:modals.notificacion-modal />
</div>
