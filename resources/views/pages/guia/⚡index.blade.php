<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Guia;

new #[Title('- Guías')] class extends Component {
    #[Computed]
    public function guiasActivas()
    {
        return Guia::activas()->recientes()->get();
    }

    public function verPdf(int $guiaId)
    {
        $this->dispatch('abrir-pdf-modal', guiaId: $guiaId);
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <x-patterns.page-header title="Guías y Documentos" tone="info"
        subtitle="Documentos oficiales para consulta y descarga." badge="Consulta">
        <x-slot:icon>
            <x-heroicon-o-clipboard-document-list class="h-6 w-6" />
        </x-slot:icon>

        <x-slot:actions>
            <div class="stats border border-base-content/10 bg-base-100/80 shadow-sm">
                <div class="stat px-4 py-3">
                    <div class="stat-title text-xs">Disponibles</div>
                    <div class="stat-value text-info text-2xl">{{ $this->guiasActivas->count() }}</div>
                </div>
            </div>
        </x-slot:actions>
    </x-patterns.page-header>

    {{-- Grid de guías activas --}}
    @if ($this->guiasActivas->isNotEmpty())
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->guiasActivas as $guia)
                <div
                    class="card border border-base-content/10 bg-base-100 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="card-body p-5 sm:p-6">
                        {{-- Icono PDF --}}
                        <div class="flex items-start gap-3">
                            <div class="avatar placeholder shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-box border border-error/20 bg-error/10 text-error">
                                    <x-heroicon-o-document class="h-6 w-6" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="line-clamp-2 text-sm font-bold leading-tight">{{ $guia->titulo }}</h3>
                                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-base-content/50">
                                    {{ $guia->categoria }}</p>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="badge badge-sm badge-info badge-soft">v{{ $guia->version }}</span>
                            <span
                                class="badge badge-sm badge-outline">{{ $guia->fecha_publicacion->format('d/m/Y') }}</span>
                            <span class="badge badge-sm badge-outline">{{ $guia->tamanio_archivo }}</span>
                        </div>

                        {{-- Acciones --}}
                        <div class="card-actions mt-5 gap-2">
                            <button wire:click="verPdf({{ $guia->id }})" class="btn btn-info btn-sm flex-1 gap-2">
                                <x-heroicon-o-eye class="w-4 h-4" />
                                Ver
                            </button>
                            <a href="{{ route('guias.descargar', $guia->id) }}" class="btn btn-outline btn-sm gap-2">
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
        <div class="card border border-dashed border-base-content/20 bg-base-100 shadow-sm">
            <div class="card-body items-center py-16 text-center">
                <x-heroicon-o-document class="mb-4 h-16 w-16 text-base-content/20" />
                <h3 class="text-lg font-bold text-base-content/50">No hay guías disponibles</h3>
                <p class="mt-1 text-sm text-base-content/50">Las guías aparecerán aquí cuando sean publicadas por el
                    administrador.</p>
            </div>
        </div>
    @endif

    {{-- Modal Visor PDF (componente reutilizable) --}}
    <livewire:modals.guia-pdf-modal />
</div>
