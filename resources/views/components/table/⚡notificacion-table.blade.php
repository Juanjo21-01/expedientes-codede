<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\NotificacionEnviada;

new class extends Component {
    use WithPagination;

    #[Reactive]
    public string $search = '';

    #[Reactive]
    public string $estadoFiltro = '';

    #[Reactive]
    public string $tipoFiltro = '';

    // Resetear paginación al cambiar filtros
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedEstadoFiltro()
    {
        $this->resetPage();
    }

    public function updatedTipoFiltro()
    {
        $this->resetPage();
    }

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

    // Reintentar envío
    public function reintentar(int $id): void
    {
        $notificacion = NotificacionEnviada::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para esta acción.');
            return;
        }

        try {
            $notificacion->load(['remitente', 'expediente.municipio', 'municipio', 'tipoNotificacion']);
            \Illuminate\Support\Facades\Mail::to($notificacion->destinatario_email)->send(new \App\Mail\NotificacionMail($notificacion));

            $notificacion->marcarEnviada();
            unset($this->notificaciones);
            $this->dispatch('notificacion-reenviada');
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Notificación reenviada correctamente.');
        } catch (\Exception $e) {
            $notificacion->marcarFallida();
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al reenviar: ' . $e->getMessage());
        }
    }

    #[On('notificacion-enviada')]
    #[On('notificacion-reenviada')]
    public function refrescar()
    {
        unset($this->notificaciones);
    }
};
?>

<div>
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
        <table class="table table-zebra table-sm">
            <thead></thead>
            <tr class="bg-base-200">
                <th class="whitespace-nowrap">Estado</th>
                <th class="whitespace-nowrap">Tipo</th>
                <th class="min-w-[200px]">Asunto</th>
                <th class="min-w-[150px]">Destinatario</th>
                <th class="whitespace-nowrap">Contexto</th>
                <th class="whitespace-nowrap">Remitente</th>
                <th class="whitespace-nowrap">Fecha</th>
                <th class="text-center whitespace-nowrap min-w-[100px]">Acciones</th>
            </tr>
            </thead>
            <tbody>
                @forelse ($this->notificaciones as $noti)
                    <tr class="hover" wire:key="noti-{{ $noti->id }}">
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
                            <div class="max-w-xs">
                                <span class="text-sm font-medium truncate block" title="{{ $noti->asunto }}">
                                    {{ Str::limit($noti->asunto, 40) }}
                                </span>
                            </div>
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
                                <a href="{{ route('expedientes.show', $noti->expediente->id) }}" wire:navigate
                                    class="link link-primary text-xs">
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
                        <td class="text-sm text-base-content/70">
                            {{ $noti->created_at->format('d/m/Y H:i') }}
                        </td>

                        {{-- Acciones --}}
                        <td>
                            <div class="flex justify-center items-center gap-1">
                                {{-- Ver detalle --}}
                                <div class="tooltip" data-tip="Ver mensaje">
                                    <button type="button" class="btn btn-ghost btn-sm btn-square text-info"
                                        @click="$dispatch('ver-detalle-notificacion', { notificacionId: {{ $noti->id }} })">
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </button>
                                </div>

                                {{-- Reintentar (solo admin, solo fallidas) --}}
                                @if (auth()->user()->isAdmin() && $noti->fallo())
                                    <div class="tooltip" data-tip="Reintentar envío">
                                        <button wire:click="reintentar({{ $noti->id }})"
                                            wire:confirm="¿Reintentar el envío de esta notificación?"
                                            class="btn btn-ghost btn-sm btn-square text-warning">
                                            <x-heroicon-o-arrow-path class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2">
                                <x-heroicon-o-envelope class="w-12 h-12 text-base-content/30" />
                                <span class="text-base-content/50">No se encontraron notificaciones</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if ($this->notificaciones->hasPages())
        <div class="border-t border-base-content/5 px-4 py-3">
            {{ $this->notificaciones->links() }}
        </div>
    @endif





</div>
