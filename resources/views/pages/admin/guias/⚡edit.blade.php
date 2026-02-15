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

<div>
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm mb-6">
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
    <div class="flex items-center gap-3 mb-6">
        <div class="bg-warning/10 text-warning rounded-btn p-2">
            <x-heroicon-o-pencil-square class="w-6 h-6" />
        </div>
        <div>
            <h1 class="text-2xl font-bold">Editar Guía</h1>
            <p class="text-base-content/60 text-sm">
                {{ $guia->titulo }} · v{{ $guia->version }} · {{ $guia->categoria }}
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5">
        <div class="card-body">
            <livewire:forms.guia-form :guiaId="$guia->id" />
        </div>
    </div>
</div>
