<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\NotificacionEnviada;
use App\Models\TipoNotificacion;

new #[Title('- Mis Notificaciones')] class extends Component {
    #[Url]
    public ?string $search = '';

    #[Url]
    public ?string $estadoFiltro = '';

    #[Url]
    public ?string $tipoFiltro = '';

    #[On('notificacion-enviada')]
    #[On('notificacion-reenviada')]
    public function refrescar(): void
    {
        unset($this->estadisticas);
    }

    #[Computed]
    public function estadisticas()
    {
        $user = auth()->user();
        $base = NotificacionEnviada::query();

        if ($user->isMunicipal()) {
            $base->recibidasPorMunicipal($user);
        } else {
            $base->accesiblesPor($user);
        }

        return [
            'total' => (clone $base)->count(),
            'enviadas' => (clone $base)->enviadas()->count(),
            'pendientes' => (clone $base)->pendientes()->count(),
            'fallidas' => (clone $base)->fallidas()->count(),
        ];
    }

    #[Computed]
    public function modoTabla(): string
    {
        return auth()->user()->isMunicipal() ? 'municipal' : 'general';
    }

    #[Computed]
    public function tiposNotificacion()
    {
        return TipoNotificacion::ordenados()->get();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estadoFiltro = '';
        $this->tipoFiltro = '';
    }
};
?>

<div class="space-y-6">
    <div class="breadcrumbs text-sm">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-base-content/60 hover:text-primary">
                    <x-heroicon-o-home class="w-4 h-4 mr-1" />
                    Inicio
                </a>
            </li>
            <li><span class="text-primary font-medium">Mis Notificaciones</span></li>
        </ul>
    </div>

    @php
        $notificacionesSubtitle = auth()->user()->isMunicipal()
            ? 'Historial de correos enviados a tu municipalidad'
            : 'Historial de tus notificaciones y seguimiento de envíos';
    @endphp

    <x-patterns.page-header title="Mis Notificaciones" :subtitle="$notificacionesSubtitle" tone="info" badge="Seguimiento de envíos">
        <x-slot:icon>
            <x-heroicon-o-envelope class="w-6 h-6" />
        </x-slot:icon>
    </x-patterns.page-header>

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

    <x-patterns.filter-card title="Filtros de búsqueda" description="Filtra por contenido, tipo o estado"
        tone="info">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="input input-sm border-base-content/20 focus-within:border-info">
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

            <select wire:model.live="tipoFiltro"
                class="select select-sm w-full border-base-content/20 focus:border-info">
                <option value="">Todos los tipos</option>
                @foreach ($this->tiposNotificacion as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <select wire:model.live="estadoFiltro"
                    class="select select-sm flex-1 border-base-content/20 focus:border-info">
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

    <livewire:table.notificacion-table :search="$search ?? ''" :estadoFiltro="$estadoFiltro ?? ''" :tipoFiltro="$tipoFiltro ?? ''" :modo="$this->modoTabla" />

    <livewire:modals.notificacion-show-modal />
</div>
