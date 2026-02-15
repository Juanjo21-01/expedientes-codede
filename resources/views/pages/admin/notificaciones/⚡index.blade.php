<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\NotificacionEnviada;
use App\Models\TipoNotificacion;

new #[Title('- Historial de Notificaciones')] class extends Component {
    // Filtros
    #[Url]
    public string $search = '';

    #[Url]
    public string $estadoFiltro = '';

    #[Url]
    public string $tipoFiltro = '';

    // Refrescar al enviar nueva notificación
    #[On('notificacion-enviada')]
    #[On('notificacion-reenviada')]
    public function refrescar(): void
    {
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

    // Limpiar filtros
    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'estadoFiltro', 'tipoFiltro']);
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                <div class="bg-primary/10 text-primary rounded-btn p-2">
                    <x-heroicon-o-envelope class="w-6 h-6" />
                </div>
                Historial de Notificaciones
            </h1>
            <p class="text-base-content/60  text-sm mt-1">Registro de correos electrónicos enviados desde el sistema</p>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Enviadas</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['enviadas'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Pendientes</div>
            <div class="stat-value text-lg text-warning">{{ $this->estadisticas['pendientes'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow-sm border border-base-content/5 rounded-box p-3">
            <div class="stat-title text-xs">Fallidas</div>
            <div class="stat-value text-lg text-error">{{ $this->estadisticas['fallidas'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5 mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- Búsqueda --}}
                <div class="sm:col-span-2">
                    <label class="input input-sm">
                        <x-heroicon-o-magnifying-glass class="h-[1em] opacity-50" />
                        <input type="text" wire:model.live.debounce.300ms="search" class="grow"
                            placeholder="Buscar por asunto, destinatario, mensaje..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-ghost btn-xs btn-circle">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </label>
                </div>

                {{-- Filtro Tipo --}}
                <select wire:model.live="tipoFiltro" class="select select-sm w-full">
                    <option value="">Todos los tipos</option>
                    @foreach ($this->tiposNotificacion as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>

                {{-- Filtro Estado --}}
                <div class="flex gap-2">
                    <select wire:model.live="estadoFiltro" class="select select-sm flex-1">
                        <option value="">Todos los estados</option>
                        @foreach (NotificacionEnviada::getEstados() as $estado)
                            <option value="{{ $estado }}">{{ $estado }}</option>
                        @endforeach
                    </select>
                    <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm btn-square tooltip tooltip-left"
                        data-tip="Limpiar filtros">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <livewire:table.notificacion-table :search="$search" :estadoFiltro="$estadoFiltro" :tipoFiltro="$tipoFiltro" />

    {{-- Modal detalle --}}
    <livewire:modals.notificacion-show-modal />
</div>
