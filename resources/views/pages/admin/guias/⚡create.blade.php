<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('- Subir Guía')] class extends Component {
    //
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
                <span class="font-medium text-primary">Subir nueva guía</span>
            </li>
        </ul>
    </div>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="bg-primary/10 text-primary rounded-btn p-2">
            <x-heroicon-o-arrow-up-tray class="w-6 h-6" />
        </div>
        <div>
            <h1 class="text-2xl font-bold">Subir Nueva Guía</h1>
            <p class="text-base-content/60 text-sm">Subir un documento PDF al repositorio de guías</p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card bg-base-100 shadow-sm border border-base-content/5">
        <div class="card-body">
            <livewire:forms.guia-form />
        </div>
    </div>
</div>
