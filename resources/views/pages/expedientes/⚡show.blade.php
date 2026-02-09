<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Expediente;
use App\Models\Role;

new #[Title('- Detalle Expediente')] class extends Component {
    public Expediente $expediente;

    public function mount(Expediente $expediente)
    {
        $this->expediente = $expediente->load(['municipio', 'responsable.role', 'tipoSolicitud', 'revisionesFinancieras.revisor']);
    }

    #[On('expediente-estado-cambiado')]
    #[On('expediente-guardado')]
    public function refrescar()
    {
        $this->expediente = $this->expediente->fresh(['municipio', 'responsable.role', 'tipoSolicitud', 'revisionesFinancieras.revisor']);
    }

    // Enviar a revisión (Técnico)
    public function enviarARevision()
    {
        $user = auth()->user();
        if (!$user->can('enviarRevision', $this->expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para esta acción.');
            return;
        }

        $this->expediente->marcarEnRevision();
        $this->expediente = $this->expediente->fresh(['municipio', 'responsable.role', 'tipoSolicitud', 'revisionesFinancieras.revisor']);
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Expediente enviado a revisión financiera.');
    }

    // Revisiones financieras ordenadas
    #[Computed]
    public function revisiones()
    {
        return $this->expediente->revisionesFinancieras()->with('revisor')->orderByDesc('fecha_revision')->get();
    }

    // Días desde recibido
    #[Computed]
    public function diasTranscurridos()
    {
        // Si no hay fecha_recibido, retornar 0 días, sino calcular la diferencia con la fecha actual
        if (!$this->expediente->fecha_recibido) {
            return 0;
        }

        // formatear la fecha_recibido para mostrarla en el timeline, pero usar la fecha original para el cálculo
        $fechaRecibido = $this->expediente->fecha_recibido->startOfDay();
        $fechaActual = now()->startOfDay();
        return $fechaRecibido->diffInDays($fechaActual);
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
                <span class="font-medium font-mono text-primary">{{ $expediente->codigo_snip }}</span>
            </li>
        </ul>
    </div>

    {{-- Header con estado y acciones --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5 mb-6">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row justify-between gap-4">
                {{-- Informacion inicial --}}
                <div class="flex-1">
                    <div class="flex items-start gap-4">
                        <div class="avatar placeholder shrink-0">
                            <div
                                class="bg-primary/10 text-primary rounded-lg w-14 h-14 flex items-center justify-center">
                                <x-heroicon-o-document-text class="w-7 h-7" />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="tooltip" data-tip="Código SNIP">
                                    <h1 class="text-xl font-bold font-mono">{{ $expediente->codigo_snip }}</h1>
                                </span>
                                <span class="badge badge-lg {{ $expediente->estado_badge_class }} tooltip"
                                    data-tip="Estado actual">
                                    {{ $expediente->estado }}
                                </span>
                                @php
                                    $tipoBadge = match ($expediente->ordinario_extraordinario) {
                                        'ORDINARIO' => 'badge-ghost',
                                        'EXTRAORDINARIO' => 'badge-accent badge-outline',
                                        'ASIGNACION EXTRAORDINARIA' => 'badge-secondary badge-outline',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <span class="badge badge-lg {{ $tipoBadge }} tooltip" data-tip="Tipo de solicitud">
                                    {{ $expediente->ordinario_extraordinario }}
                                </span>
                            </div>
                            <h2 class="text-lg mt-1">{{ $expediente->nombre_proyecto }}</h2>
                            <div class="flex items-center gap-4 mt-2 text-sm text-base-content/60">
                                <span>{{ $expediente->municipio->nombre }}</span>
                                <span>·</span>
                                <span>{{ $expediente->tipoSolicitud->nombre }}</span>
                                <span>·</span>
                                <span>{{ $this->diasTranscurridos }} días desde recibido</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Acciones contextuales --}}
                <div class="flex flex-wrap gap-2 items-start">
                    {{-- Enviar a revisión (Técnico) --}}
                    @can('enviarRevision', $expediente)
                        <button wire:click="enviarARevision" wire:confirm="¿Enviar este expediente a revisión financiera?"
                            class="btn btn-primary btn-sm gap-2">
                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                            Enviar a Revisión
                        </button>
                    @endcan

                    {{-- Revisar (Jefe Financiero / Admin) --}}
                    @can('revisarFinanciera', $expediente)
                        <a href="{{ route('expedientes.revision', $expediente->id) }}" wire:navigate
                            class="btn btn-accent btn-sm gap-2">
                            <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                            Registrar Revisión
                        </a>
                    @endcan

                    {{-- Editar --}}
                    @can('update', $expediente)
                        <a href="{{ route('expedientes.edit', $expediente->id) }}" wire:navigate
                            class="btn btn-warning btn-sm gap-2">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </a>
                    @endcan

                    {{-- Volver al listado --}}
                    <a href="{{ route('expedientes.index') }}" wire:navigate class="btn btn-ghost btn-sm gap-2">
                        <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid de información --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Columna izquierda: Información del expediente --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Datos del Proyecto --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body">
                    <h3 class="card-title text-base gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                        Información del Proyecto
                    </h3>
                    <div class="divider my-1"></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-base-content/50 uppercase">Código SNIP</span>
                            <p class="font-mono font-bold">{{ $expediente->codigo_snip }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 uppercase">Fecha Recibido</span>
                            <p class="font-medium">{{ $expediente->fecha_recibido->format('d/m/Y') }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-xs text-base-content/50 uppercase">Nombre del Proyecto</span>
                            <p class="font-medium">{{ $expediente->nombre_proyecto }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 uppercase">Tipo de Solicitud</span>
                            <p>{{ $expediente->tipoSolicitud->nombre }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 uppercase">Tipo</span>
                            <p>{{ ucfirst(strtolower($expediente->ordinario_extraordinario)) }}</p>
                        </div>
                        @if ($expediente->observaciones)
                            <div class="sm:col-span-2">
                                <span class="text-xs text-base-content/50 uppercase">Observaciones</span>
                                <p class="text-sm">{{ $expediente->observaciones }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Información Financiera --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body">
                    <h3 class="card-title text-base gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-primary" />
                        Información Financiera
                    </h3>
                    <div class="divider my-1"></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-base-content/50 uppercase">Monto del Contrato</span>
                            <p class="font-bold text-lg">{{ $expediente->monto_formateado }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 uppercase">Adjudicatario</span>
                            <p>{{ $expediente->adjudicatario ?? 'N/A' }}</p>
                        </div>
                        @if ($expediente->fecha_aprobacion)
                            <div>
                                <span class="text-xs text-base-content/50 uppercase">Fecha de Aprobación</span>
                                <p class="font-medium text-success">
                                    {{ $expediente->fecha_aprobacion->format('d/m/Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Revisiones Financieras --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body">
                    <h3 class="card-title text-base gap-2">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-primary" />
                        Revisiones Financieras
                        @if ($this->revisiones->isNotEmpty())
                            <span class="badge badge-sm badge-primary">{{ $this->revisiones->count() }}</span>
                        @endif
                    </h3>
                    <div class="divider my-1"></div>

                    @if ($this->revisiones->isNotEmpty())
                        <div class="space-y-4">
                            @foreach ($this->revisiones as $revision)
                                <div
                                    class="border border-base-content/5 rounded-lg p-4 {{ $loop->first ? 'bg-base-200/50' : '' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="badge {{ $revision->estado_badge_class }}">{{ $revision->estado }}</span>
                                            @if ($revision->tieneAccion())
                                                <span
                                                    class="badge {{ $revision->accion_badge_class }}">{{ $revision->accion_texto }}</span>
                                            @endif
                                        </div>
                                        <span
                                            class="text-xs text-base-content/50">{{ $revision->fecha_revision->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="mt-2 text-sm">
                                        <span class="text-base-content/60">Revisor:</span>
                                        <span class="font-medium">{{ $revision->revisor->nombre_completo }}</span>
                                    </div>
                                    @if ($revision->monto_aprobado)
                                        <div class="text-sm mt-1">
                                            <span class="text-base-content/60">Monto aprobado:</span>
                                            <span class="font-bold">{{ $revision->monto_formateado }}</span>
                                        </div>
                                    @endif
                                    @if ($revision->observaciones)
                                        <div class="mt-2 text-sm bg-base-200 rounded p-2">
                                            {{ $revision->observaciones }}
                                        </div>
                                    @endif
                                    @if ($revision->tieneComplemento())
                                        <div class="text-xs text-base-content/50 mt-2">
                                            Complemento: {{ $revision->fecha_complemento->format('d/m/Y') }} ·
                                            {{ $revision->dias_transcurridos }} días
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-base-content/20 mx-auto mb-2" />
                            <p class="text-base-content/40 text-sm">Aún no hay revisiones financieras</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: Sidebar --}}
        <div class="space-y-6">
            {{-- Municipio --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <x-heroicon-o-building-office-2 class="w-4 h-4 text-primary" />
                        Municipio
                    </h3>
                    <div class="mt-2 flex justify-between items-start gap-3">
                        <div>
                            <p class="font-bold">{{ $expediente->municipio->nombre }}</p>
                            <p class="text-sm text-base-content/60">{{ $expediente->municipio->departamento }}</p>
                            @if ($expediente->municipio->contacto_nombre)
                                <div class="divider my-1"></div>
                                <p class="text-xs text-base-content/50">Contacto</p>
                                <p class="text-sm">{{ $expediente->municipio->contacto_nombre }}</p>
                                @if ($expediente->municipio->contacto_telefono)
                                    <p class="text-xs text-base-content/60">
                                        {{ $expediente->municipio->contacto_telefono }}</p>
                                @endif
                            @endif
                        </div>
                        @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                            <a href="{{ route('admin.municipios.show', $expediente->municipio->id) }}" wire:navigate
                                class="btn btn-ghost btn-sm btn-circle" title="Ver perfil">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Responsable --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <x-heroicon-o-user class="w-4 h-4 text-primary" />
                        Responsable
                    </h3>
                    <div class="mt-2 flex justify-between items-start gap-3">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-neutral text-neutral-content rounded-full w-10 h-10 flex items-center justify-center">
                                    <span class="text-sm">{{ $expediente->responsable->iniciales }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $expediente->responsable->nombre_completo }}</p>
                                <p class="text-xs text-base-content/60">{{ $expediente->responsable->role->nombre }}
                                </p>
                            </div>
                        </div>
                        @if (auth()->user()->isAdmin() || auth()->user()->isDirector())
                            <a href="{{ route('admin.usuarios.show', $expediente->responsable->id) }}" wire:navigate
                                class="btn btn-ghost btn-sm btn-circle" title="Ver perfil">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Línea de Tiempo --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <x-heroicon-o-clock class="w-4 h-4 text-primary" />
                        Fechas Importantes
                    </h3>
                    <div class="mt-3">
                        <ul class="timeline timeline-vertical timeline-compact timeline-snap-icon">
                            <li>
                                <div class="timeline-middle">
                                    <x-heroicon-s-check-circle class="w-4 h-4 text-info" />
                                </div>
                                <div class="timeline-end mb-4">
                                    <div class="text-xs text-base-content/50">Recibido</div>
                                    <div class="text-sm font-medium">
                                        {{ $expediente->fecha_recibido->format('d/m/Y') }}</div>
                                </div>
                                <hr />
                            </li>
                            @if ($expediente->updated_at && $expediente->updated_at != $expediente->created_at)
                                <li>
                                    <hr />
                                    <div class="timeline-middle">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-warning" />
                                    </div>
                                    <div class="timeline-end mb-4">
                                        <div class="text-xs text-base-content/50">Última actualización</div>
                                        <div class="text-sm font-medium">
                                            {{ $expediente->updated_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <hr />
                                </li>
                            @endif
                            @if ($expediente->fecha_aprobacion)
                                <li>
                                    <hr />
                                    <div class="timeline-middle">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-success" />
                                    </div>
                                    <div class="timeline-end mb-4">
                                        <div class="text-xs text-base-content/50">Aprobado</div>
                                        <div class="text-sm font-medium text-success">
                                            {{ $expediente->fecha_aprobacion->format('d/m/Y') }}</div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Metadatos --}}
            <div class="card bg-base-100 shadow-sm border border-base-content/5">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-primary" />
                        Información del Sistema
                    </h3>
                    <div class="mt-2 space-y-2 text-xs text-base-content/60">
                        <div class="flex justify-between">
                            <span>Creado</span>
                            <span>{{ $expediente->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Última mod.</span>
                            <span>{{ $expediente->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>ID</span>
                            <span class="font-mono">{{ $expediente->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modales Admin --}}
    @if (auth()->user()->isAdmin())
        <livewire:modals.expediente-estado-modal />
        <livewire:modals.expediente-delete-modal />
    @endif
</div>
