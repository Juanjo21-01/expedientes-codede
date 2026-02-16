<?php

use App\Models\Bitacora;
use Livewire\Component;

new class extends Component {
    public bool $mostrarDetalle = false;
    public ?array $registroDetalle = null;

    public function verDetalle(int $id): void
    {
        $registro = Bitacora::with(['user.role'])->find($id);

        if (!$registro) {
            return;
        }

        $this->registroDetalle = [
            'id' => $registro->id,
            'usuario' => $registro->user?->nombre_completo ?? 'Sistema',
            'usuario_email' => $registro->user?->email ?? '-',
            'usuario_rol' => $registro->user?->role?->nombre ?? '-',
            'entidad' => $registro->entidad,
            'entidad_id' => $registro->entidad_id,
            'entidad_badge' => $registro->entidad_badge_class,
            'tipo' => $registro->tipo,
            'tipo_badge' => $registro->tipo_badge_class,
            'icono' => $registro->icono_tipo,
            'detalle' => $registro->detalle,
            'fecha' => $registro->created_at->format('d/m/Y'),
            'hora' => $registro->created_at->format('H:i:s'),
            'hace' => $registro->created_at->diffForHumans(),
        ];

        $this->mostrarDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->mostrarDetalle = false;
        $this->registroDetalle = null;
    }
};
?>

<div x-on:ver-detalle-bitacora.window="$wire.verDetalle($event.detail.registroId)">
    @if ($mostrarDetalle && $registroDetalle)
        <div class="modal modal-open" wire:keydown.escape.window="cerrarDetalle">
            <div class="modal-box max-w-lg" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                        Detalle del Registro
                    </h3>
                    <button wire:click="cerrarDetalle" class="btn btn-ghost btn-sm btn-circle">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="badge {{ $registroDetalle['entidad_badge'] }} badge-outline">
                            {{ $registroDetalle['entidad'] }}
                        </span>
                        <span class="badge {{ $registroDetalle['tipo_badge'] }}">
                            {{ $registroDetalle['tipo'] }}
                        </span>
                        @if ($registroDetalle['entidad_id'])
                            <span class="badge badge-ghost badge-sm">ID: {{ $registroDetalle['entidad_id'] }}</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-base-200/50 rounded-lg p-3">
                            <p class="text-xs text-base-content/50 mb-1">Fecha</p>
                            <p class="font-medium text-sm">{{ $registroDetalle['fecha'] }}</p>
                        </div>
                        <div class="bg-base-200/50 rounded-lg p-3">
                            <p class="text-xs text-base-content/50 mb-1">Hora</p>
                            <p class="font-medium text-sm">{{ $registroDetalle['hora'] }}</p>
                        </div>
                    </div>

                    <div class="bg-base-200/50 rounded-lg p-3">
                        <p class="text-xs text-base-content/50 mb-1">Realizado por</p>
                        <div class="flex items-center gap-2">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-neutral text-neutral-content rounded-full w-8 h-8 flex items-center justify-center">
                                    <span class="text-xs">{{ substr($registroDetalle['usuario'], 0, 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $registroDetalle['usuario'] }}</p>
                                <p class="text-xs text-base-content/50">{{ $registroDetalle['usuario_email'] }} ·
                                    {{ $registroDetalle['usuario_rol'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 rounded-lg p-3">
                        <p class="text-xs text-base-content/50 mb-1">Detalle de la acción</p>
                        <p class="text-sm leading-relaxed">{{ $registroDetalle['detalle'] }}</p>
                    </div>

                    <div class="text-center">
                        <span class="text-xs text-base-content/40">{{ $registroDetalle['hace'] }}</span>
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="cerrarDetalle" class="btn btn-sm">Cerrar</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cerrarDetalle"></div>
        </div>
    @endif
</div>
