<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\NotificacionEnviada;

new class extends Component {
    public bool $abierto = false;

    // Datos de la notificación
    public string $asunto = '';
    public string $mensaje = '';
    public string $estado = '';
    public string $estadoClass = '';
    public string $tipo = '';
    public string $destinatarioNombre = '';
    public string $destinatarioEmail = '';
    public string $remitente = '';
    public ?string $contexto = null;
    public string $creadoAt = '';
    public ?string $enviadoAt = null;

    #[On('ver-detalle-notificacion')]
    public function abrir(int $notificacionId): void
    {
        $noti = NotificacionEnviada::with(['remitente', 'tipoNotificacion', 'expediente', 'municipio'])->findOrFail($notificacionId);

        $this->asunto = $noti->asunto;
        $this->mensaje = $noti->mensaje;
        $this->estado = $noti->estado;
        $this->estadoClass = $noti->estado_badge_class;
        $this->tipo = $noti->tipoNotificacion->nombre ?? 'N/A';
        $this->destinatarioNombre = $noti->destinatario_nombre ?? 'N/A';
        $this->destinatarioEmail = $noti->destinatario_email;
        $this->remitente = $noti->remitente->nombre_completo ?? 'Sistema';
        $this->contexto = $noti->expediente ? 'SNIP ' . $noti->expediente->codigo_snip . ' — ' . $noti->expediente->nombre_proyecto : ($noti->municipio ? $noti->municipio->nombre : null);
        $this->creadoAt = $noti->created_at->format('d/m/Y H:i');
        $this->enviadoAt = $noti->enviado_at?->format('d/m/Y H:i');

        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
    }
};
?>

<div x-on:ver-detalle-notificacion.window="$wire.abrir($event.detail.notificacionId)">
    @if ($abierto)
        <div class="modal modal-open">
            <div class="modal-box max-w-lg">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="rounded-btn bg-info/10 p-2">
                            <x-heroicon-o-envelope class="w-5 h-5 text-info" />
                        </div>
                        <h3 class="font-bold text-base truncate">{{ $asunto }}</h3>
                    </div>
                    <button class="btn btn-sm btn-circle btn-ghost" wire:click="cerrar">✕</button>
                </div>

                <div class="divider my-2"></div>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="badge badge-sm {{ $estadoClass }}">{{ $estado }}</span>
                    <span class="badge badge-sm badge-outline">{{ $tipo }}</span>
                </div>

                {{-- Destinatario --}}
                <div class="bg-base-200/50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-base-content/50 uppercase tracking-wide font-semibold mb-1">Destinatario</p>
                    <p class="text-sm font-medium">{{ $destinatarioNombre }}</p>
                    <p class="text-xs text-base-content/60">{{ $destinatarioEmail }}</p>
                </div>

                {{-- Mensaje --}}
                <div class="mb-4">
                    <p class="text-xs text-base-content/50 uppercase tracking-wide font-semibold mb-1">Mensaje</p>
                    <div class="bg-base-200/50 rounded-lg p-3 border-l-3 border-primary">
                        <p class="text-sm whitespace-pre-line leading-relaxed">{{ $mensaje }}</p>
                    </div>
                </div>

                {{-- Contexto --}}
                @if ($contexto)
                    <div class="bg-base-200/50 rounded-lg p-3 mb-4">
                        <p class="text-xs text-base-content/50 uppercase tracking-wide font-semibold mb-1">Contexto</p>
                        <p class="text-sm">{{ $contexto }}</p>
                    </div>
                @endif

                {{-- Info adicional --}}
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-base-content/50">Remitente</p>
                        <p class="font-medium">{{ $remitente }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50">Fecha de creación</p>
                        <p class="font-medium">{{ $creadoAt }}</p>
                    </div>
                    @if ($enviadoAt)
                        <div>
                            <p class="text-xs text-base-content/50">Fecha de envío</p>
                            <p class="font-medium">{{ $enviadoAt }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cerrar"></div>
        </div>
    @endif
</div>
