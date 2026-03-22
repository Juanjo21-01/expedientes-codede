<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Guia;

new #[Title('- Editar Guía')] class extends Component {
    public Guia $guia;

    public function mount(Guia $guia)
    {
        $this->guia = $guia;
    }
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm">
        <ul>
            <li>
                <a href="{{ route('admin.guias.index') }}" wire:navigate
                    class="font-medium text-base-content/60 hover:text-primary">
                    <x-heroicon-o-document-check class="w-4 h-4 mr-1" />
                    Gestión de Guías
                </a>
            </li>
            <li>
                <span class="font-medium text-base-content/60">{{ $guia->titulo_completo }}</span>
            </li>
            <li>
                <span class="font-medium text-primary">Editar</span>
            </li>
        </ul>
    </div>

    {{-- Header --}}
    <div
        class="rounded-box border border-warning/20 bg-linear-to-br from-warning/10 via-base-100 to-base-100 p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <div class="rounded-box border border-warning/20 bg-warning/15 p-2.5 text-warning">
                <x-heroicon-o-pencil-square class="h-6 w-6" />
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Editar Guía</h1>
                <p class="text-sm text-base-content/70">
                    {{ $guia->titulo }} · v{{ $guia->version }} · {{ $guia->categoria }}
                </p>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card border border-base-content/10 bg-base-100 shadow-sm">
        <div class="card-body">
            <livewire:forms.guia-form :guiaId="$guia->id" />
        </div>
    </div>
</div>
