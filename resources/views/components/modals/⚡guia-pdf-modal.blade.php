<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Guia;

new class extends Component {
    public bool $mostrar = false;
    public ?int $guiaId = null;
    public string $titulo = '';
    public string $categoria = '';
    public int $version = 0;
    public string $fecha = '';
    public string $urlPdf = '';

    #[On('abrir-pdf-modal')]
    public function abrir(int $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $this->guiaId = $guia->id;
        $this->titulo = $guia->titulo;
        $this->categoria = $guia->categoria;
        $this->version = $guia->version;
        $this->fecha = $guia->fecha_publicacion->format('d/m/Y');
        $this->urlPdf = $guia->url_pdf;
        $this->mostrar = true;
    }

    public function cerrar()
    {
        $this->mostrar = false;
        $this->reset(['guiaId', 'titulo', 'categoria', 'version', 'fecha', 'urlPdf']);
    }
};
?>

<div>
    @if ($mostrar)
        <div class="modal modal-open">
            <div class="modal-box max-w-6xl w-11/12 h-[90vh] flex flex-col p-0">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-base-content/5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="bg-error/10 text-error rounded-lg w-8 h-8 flex items-center justify-center shrink-0">
                            <x-heroicon-o-document class="w-4 h-4" />
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-sm truncate">{{ $titulo }}</h3>
                            <div class="flex items-center gap-2 text-xs text-base-content/60">
                                <span>{{ $categoria }}</span>
                                <span>&middot;</span>
                                <span>v{{ $version }}</span>
                                <span>&middot;</span>
                                <span>{{ $fecha }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ $urlPdf }}" download class="btn btn-sm btn-ghost gap-2">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            Descargar
                        </a>
                        <a href="{{ $urlPdf }}" target="_blank" class="btn btn-sm btn-ghost gap-2">
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            Abrir
                        </a>
                        <button wire:click="cerrar" class="btn btn-sm btn-circle btn-ghost">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                {{-- Visor PDF --}}
                <div class="flex-1 bg-base-200">
                    <embed src="{{ $urlPdf }}" type="application/pdf" class="w-full h-full" />
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cerrar"></div>
        </div>
    @endif
</div>
