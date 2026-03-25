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
    public ?string $search = '';

    #[Url]
    public ?string $estadoFiltro = '';

    #[Url]
    public ?string $tipoFiltro = '';

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
        $this->search = '';
        $this->estadoFiltro = '';
        $this->tipoFiltro = '';
    }
};
?>

<div class="space-y-6">
    <x-patterns.page-header title="Historial de Notificaciones"
        subtitle="Registro de correos electrónicos enviados desde el sistema" tone="primary" badge="Panel administrativo">
        <x-slot:icon>
            <x-heroicon-o-envelope class="w-6 h-6" />
        </x-slot:icon>
    </x-patterns.page-header>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="stat bg-base-100 shadow-sm border border-base-content/10 rounded-box p-3">
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-lg">{{ $this->estadisticas['total'] }}</div>
        </div>
        <div class="stat bg-success/5 shadow-sm border border-success/20 rounded-box p-3">
            <div class="stat-title text-xs">Enviadas</div>
            <div class="stat-value text-lg text-success">{{ $this->estadisticas['enviadas'] }}</div>
        </div>
        <div class="stat bg-warning/5 shadow-sm border border-warning/20 rounded-box p-3">
            <div class="stat-title text-xs">Pendientes</div>
            <div class="stat-value text-lg text-warning">{{ $this->estadisticas['pendientes'] }}</div>
        </div>
        <div class="stat bg-error/5 shadow-sm border border-error/20 rounded-box p-3">
            <div class="stat-title text-xs">Fallidas</div>
            <div class="stat-value text-lg text-error">{{ $this->estadisticas['fallidas'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-patterns.filter-card title="Filtros de búsqueda" description="Acota por texto, tipo o estado de envío"
        tone="primary">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Búsqueda --}}
            <div class="sm:col-span-2">
                <label class="input input-sm border-base-content/20 focus-within:border-primary">
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
            <select wire:model.live="tipoFiltro"
                class="select select-sm w-full border-base-content/20 focus:border-primary">
                <option value="">Todos los tipos</option>
                @foreach ($this->tiposNotificacion as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
            </select>

            {{-- Filtro Estado --}}
            <div class="flex gap-2">
                <select wire:model.live="estadoFiltro"
                    class="select select-sm flex-1 border-base-content/20 focus:border-primary">
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
    </x-patterns.filter-card>

    {{-- Tabla --}}
    <livewire:table.notificacion-table :search="$search ?? ''" :estadoFiltro="$estadoFiltro ?? ''" :tipoFiltro="$tipoFiltro ?? ''" />

    {{-- Modal detalle --}}
    <livewire:modals.notificacion-show-modal />
</div>
