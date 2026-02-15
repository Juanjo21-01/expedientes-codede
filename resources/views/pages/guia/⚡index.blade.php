<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Guia;

new #[Title('- Guías')] class extends Component {
    public ?int $guiaSeleccionadaId = null;
    public bool $modalAbierta = false;

    #[Computed]
    public function guiasActivas()
    {
        return Guia::activas()->recientes()->get();
    }

    public function verPdf(int $guiaId)
    {
        $this->guiaSeleccionadaId = $guiaId;
        $this->modalAbierta = true;
    }

    #[Computed]
    public function guiaSeleccionada()
    {
        if (!$this->guiaSeleccionadaId) return null;
        return Guia::find($this->guiaSeleccionadaId);
    }

    public function cerrarModal()
    {
        $this->modalAbierta = false;
        $this->guiaSeleccionadaId = null;
    }
};
?>

<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="avatar placeholder">
            <div class="bg-primary/10 text-primary rounded-lg w-12 h-12 flex items-center justify-center">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
            </div>
        </div>
        <div>
            <h1 class="text-2xl font-bold">Guías y Documentos</h1>
            <p class="text-base-content/60 text-sm">Documentos disponibles para consulta y descarga</p>
        </div>
    </div>

    {{-- Grid de guías activas --}}
    @if ($this->guiasActivas->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($this->guiasActivas as $guia)
                <div class="card bg-base-100 shadow-sm border border-base-content/5 hover:shadow-md transition-shadow">
                    <div class="card-body p-5">
                        {{-- Icono PDF --}}
                        <div class="flex items-start gap-3">
                            <div class="avatar placeholder shrink-0">
                                <div class="bg-error/10 text-error rounded-lg w-12 h-12">
                                    <x-heroicon-o-document class="w-6 h-6" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm leading-tight">{{ $guia->titulo }}</h3>
                                <p class="text-xs text-base-content/50 mt-1">{{ $guia->categoria }}</p>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="badge badge-sm badge-ghost">v{{ $guia->version }}</span>
                            <span
                                class="badge badge-sm badge-outline">{{ $guia->fecha_publicacion->format('d/m/Y') }}</span>
                            <span class="badge badge-sm badge-outline">{{ $guia->tamanio_archivo }}</span>
                        </div>

                        {{-- Acciones --}}
                        <div class="card-actions mt-4 gap-2">
                            <button wire:click="verPdf({{ $guia->id }})" class="btn btn-primary btn-sm flex-1 gap-2">
                                <x-heroicon-o-eye class="w-4 h-4" />
                                Ver
                            </button>
                            <a href="{{ $guia->url_pdf }}" download class="btn btn-ghost btn-sm gap-2">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                Descargar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Sin guías --}}
        <div class="card bg-base-100 shadow-sm border border-base-content/5">
            <div class="card-body items-center text-center py-16">
                <x-heroicon-o-document class="w-16 h-16 text-base-content/20 mb-4" />
                <h3 class="font-bold text-lg text-base-content/40">No hay guías disponibles</h3>
                <p class="text-base-content/40 text-sm mt-1">Las guías aparecerán aquí cuando sean publicadas por el
                    administrador.</p>
            </div>
        </div>
    @endif

    {{-- Modal Visor PDF --}}
    @if ($modalAbierta && $this->guiaSeleccionada)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-6xl w-11/12 h-[90vh] flex flex-col p-0">
                {{-- Header del modal --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-base-content/5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="avatar placeholder shrink-0">
                            <div class="bg-error/10 text-error rounded-lg w-8 h-8">
                                <x-heroicon-o-document class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-sm truncate">{{ $this->guiaSeleccionada->titulo }}</h3>
                            <div class="flex items-center gap-2 text-xs text-base-content/60">
                                <span>{{ $this->guiaSeleccionada->categoria }}</span>
                                <span>·</span>
                                <span>v{{ $this->guiaSeleccionada->version }}</span>
                                <span>·</span>
                                <span>{{ $this->guiaSeleccionada->fecha_publicacion->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ $this->guiaSeleccionada->url_pdf }}" download
                            class="btn btn-sm btn-ghost gap-2">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            Descargar
                        </a>
                        <a href="{{ $this->guiaSeleccionada->url_pdf }}" target="_blank"
                            class="btn btn-sm btn-ghost gap-2">
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            Abrir
                        </a>
                        <button wire:click="cerrarModal" class="btn btn-sm btn-circle btn-ghost">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                {{-- Visor PDF --}}
                <div class="flex-1 bg-base-200">
                    <embed src="{{ $this->guiaSeleccionada->url_pdf }}" type="application/pdf"
                        class="w-full h-full" />
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="cerrarModal">close</button>
            </form>
        </dialog>
    @endif
</div>
