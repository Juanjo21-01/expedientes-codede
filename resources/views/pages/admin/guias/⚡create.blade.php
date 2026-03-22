<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('- Subir Guía')] class extends Component {
    //
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
                <span class="font-medium text-primary">Subir nueva guía</span>
            </li>
        </ul>
    </div>

    {{-- Header --}}
    <div
        class="rounded-box border border-primary/20 bg-linear-to-br from-primary/10 via-base-100 to-base-100 p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <div class="rounded-box border border-primary/20 bg-primary/15 p-2.5 text-primary">
                <x-heroicon-o-arrow-up-tray class="h-6 w-6" />
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Subir Nueva Guía</h1>
                <p class="text-sm text-base-content/70">Carga un documento PDF al repositorio institucional de guías.
                </p>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card border border-base-content/10 bg-base-100 shadow-sm">
        <div class="card-body">
            <livewire:forms.guia-form />
        </div>
    </div>
</div>
