<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\NotificacionEnviada;
use App\Models\TipoNotificacion;

new #[Title('- Historial de Notificaciones')] class extends Component {
    use WithPagination;

    // Filtros
    #[Url]
    public string $search = '';

    #[Url]
    public string $estadoFiltro = '';

    #[Url]
    public string $tipoFiltro = '';

    // Resetear paginación al filtrar
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedTipoFiltro(): void
    {
        $this->resetPage();
    }

    // Refrescar al enviar nueva notificación
    #[On('notificacion-enviada')]
    public function refrescar(): void
    {
        $this->resetPage();
        unset($this->estadisticas);
    }

    // Estadísticas
    #[Computed]
    public function estadisticas()
    {
        $user = auth()->user();
        $base = NotificacionEnviada::query()->accesiblesPor($user);

        return [
            'total' => (clone $base)->count(),
            'enviadas' => (clone $base)->enviadas()->count(),
            'pendientes' => (clone $base)->pendientes()->count(),
            'fallidas' => (clone $base)->fallidas()->count(),
        ];
    }

    // Tipos de notificación para filtro
    #[Computed]
    public function tiposNotificacion()
    {
        return TipoNotificacion::ordenados()->get();
    }

    // Notificaciones paginadas
    #[Computed]
    public function notificaciones()
    {
        $user = auth()->user();

        return NotificacionEnviada::query()
            ->accesiblesPor($user)
            ->with(['remitente', 'tipoNotificacion', 'expediente', 'municipio'])
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->estadoFiltro, fn($q) => $q->where('estado', $this->estadoFiltro))
            ->when($this->tipoFiltro, fn($q) => $q->deTipo($this->tipoFiltro))
            ->recientes()
            ->paginate(15);
    }

    // Limpiar filtros
    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'estadoFiltro', 'tipoFiltro']);
        $this->resetPage();
    }

    // Reintentar envío
    public function reintentar(int $id): void
    {
        $notificacion = NotificacionEnviada::findOrFail($id);

        // Solo admin puede reintentar
        if (!auth()->user()->isAdmin()) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para esta acción.');
            return;
        }

        try {
            $notificacion->load(['remitente', 'expediente.municipio', 'municipio', 'tipoNotificacion']);
            \Illuminate\Support\Facades\Mail::to($notificacion->destinatario_email)->send(new \App\Mail\NotificacionExpedienteMail($notificacion));

            $notificacion->marcarEnviada();
            unset($this->estadisticas);
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Notificación reenviada correctamente.');
        } catch (\Exception $e) {
            $notificacion->marcarFallida();
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al reenviar: ' . $e->getMessage());
        }
    }
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm font-medium">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="gap-1 text-base-content/60 hover:text-primary transition-colors">
                    <x-heroicon-o-home class="w-4 h-4" />
                    Inicio
                </a>
            </li>
            <li>
                <span class="inline-flex items-center gap-1 text-primary">
                    <x-heroicon-o-envelope class="w-4 h-4" />
                    Notificaciones
                </span>
            </li>
        </ul>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-envelope class="w-6 h-6" />
                </div>
                Historial de Notificaciones
            </h1>
            <p class="text-base-content/60 mt-1">Registro de correos electrónicos enviados desde el sistema</p>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-4">
            <div class="stat-figure text-primary">
                <x-heroicon-o-envelope class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-2xl">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-4">
            <div class="stat-figure text-success">
                <x-heroicon-o-check-circle class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Enviadas</div>
            <div class="stat-value text-2xl text-success">{{ $this->estadisticas['enviadas'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-4">
            <div class="stat-figure text-warning">
                <x-heroicon-o-clock class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Pendientes</div>
            <div class="stat-value text-2xl text-warning">{{ $this->estadisticas['pendientes'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-4">
            <div class="stat-figure text-error">
                <x-heroicon-o-x-circle class="w-6 h-6" />
            </div>
            <div class="stat-title text-xs">Fallidas</div>
            <div class="stat-value text-2xl text-error">{{ $this->estadisticas['fallidas'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Búsqueda --}}
                <div class="flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/40" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                            placeholder="Buscar por asunto, destinatario, mensaje..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                        @endif
                    </label>
                </div>

                {{-- Filtro Estado --}}
                <select wire:model.live="estadoFiltro" class="select select-bordered w-full sm:w-40">
                    <option value="">Todos los estados</option>
                    @foreach (NotificacionEnviada::getEstados() as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>

                {{-- Filtro Tipo --}}
                <select wire:model.live="tipoFiltro" class="select select-bordered w-full sm:w-48">
                    <option value="">Todos los tipos</option>
                    @foreach ($this->tiposNotificacion as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>

                {{-- Limpiar --}}
                @if ($search || $estadoFiltro || $tipoFiltro)
                    <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm gap-1">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        Limpiar
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabla de notificaciones --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5">
        <div class="card-body p-0">
            @if ($this->notificaciones->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Asunto</th>
                                <th>Destinatario</th>
                                <th>Contexto</th>
                                <th>Remitente</th>
                                <th>Fecha</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->notificaciones as $noti)
                                <tr class="hover:bg-base-200/30" wire:key="noti-{{ $noti->id }}">
                                    {{-- Estado --}}
                                    <td>
                                        <span class="badge badge-sm {{ $noti->estado_badge_class }}">
                                            {{ $noti->estado }}
                                        </span>
                                    </td>

                                    {{-- Tipo --}}
                                    <td>
                                        <span class="text-xs">
                                            {{ $noti->tipoNotificacion->nombre ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- Asunto --}}
                                    <td>
                                        <span class="text-sm font-medium max-w-xs truncate block"
                                            title="{{ $noti->asunto }}">
                                            {{ Str::limit($noti->asunto, 40) }}
                                        </span>
                                    </td>

                                    {{-- Destinatario --}}
                                    <td>
                                        <div>
                                            @if ($noti->destinatario_nombre)
                                                <p class="text-sm font-medium">{{ $noti->destinatario_nombre }}</p>
                                            @endif
                                            <p class="text-xs text-base-content/60">{{ $noti->destinatario_email }}</p>
                                        </div>
                                    </td>

                                    {{-- Contexto --}}
                                    <td>
                                        @if ($noti->expediente)
                                            <a href="{{ route('expedientes.show', $noti->expediente->id) }}"
                                                wire:navigate class="link link-primary text-xs">
                                                {{ $noti->expediente->codigo_snip }}
                                            </a>
                                        @elseif ($noti->municipio)
                                            <span class="text-xs">{{ $noti->municipio->nombre }}</span>
                                        @else
                                            <span class="text-xs text-base-content/40">-</span>
                                        @endif
                                    </td>

                                    {{-- Remitente --}}
                                    <td>
                                        <span class="text-xs">
                                            {{ $noti->remitente->nombre_completo ?? 'Sistema' }}
                                        </span>
                                    </td>

                                    {{-- Fecha --}}
                                    <td>
                                        <span class="text-xs text-base-content/60">
                                            {{ $noti->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            {{-- Ver detalle (expandir) --}}
                                            <div class="dropdown dropdown-end">
                                                <div tabindex="0" role="button"
                                                    class="btn btn-ghost btn-xs btn-circle tooltip"
                                                    data-tip="Ver mensaje">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </div>
                                                <div tabindex="0"
                                                    class="dropdown-content card card-compact bg-base-100 shadow-xl border border-base-300 w-80 z-50">
                                                    <div class="card-body">
                                                        <h4 class="font-bold text-sm">{{ $noti->asunto }}</h4>
                                                        <div class="divider my-0"></div>
                                                        <p class="text-sm whitespace-pre-line">{{ $noti->mensaje }}</p>
                                                        <div class="text-xs text-base-content/50 mt-2">
                                                            @if ($noti->enviado_at)
                                                                Enviado: {{ $noti->enviado_at->format('d/m/Y H:i') }}
                                                            @else
                                                                Creado: {{ $noti->created_at->format('d/m/Y H:i') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Reintentar (solo admin, solo fallidas) --}}
                                            @if (auth()->user()->isAdmin() && $noti->fallo())
                                                <button wire:click="reintentar({{ $noti->id }})"
                                                    wire:confirm="¿Reintentar el envío de esta notificación?"
                                                    class="btn btn-ghost btn-xs btn-circle tooltip tooltip-left"
                                                    data-tip="Reintentar envío">
                                                    <x-heroicon-o-arrow-path class="w-4 h-4 text-warning" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="p-4 border-t border-base-content/5">
                    {{ $this->notificaciones->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <x-heroicon-o-envelope class="w-16 h-16 text-base-content/10 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-base-content/40">No hay notificaciones</h3>
                    <p class="text-sm text-base-content/30 mt-1">
                        @if ($search || $estadoFiltro || $tipoFiltro)
                            No se encontraron resultados con los filtros aplicados
                        @else
                            Aún no se han enviado notificaciones desde el sistema
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
