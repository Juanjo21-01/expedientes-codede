<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('- Nuevo Expediente')] class extends Component {
    //
};
?>

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm">
        <ul>
            <li>
                <a href="{{ route('expedientes.index') }}" wire:navigate
                    class="font-medium text-base-content/60 hover:text-primary">
                    <x-heroicon-o-folder class="w-4 h-4 mr-1" />
                    Expedientes
                </a>
            </li>
            <li>
                <span class="font-medium text-primary">Nuevo Expediente</span>
            </li>
        </ul>
    </div>

    {{-- Header --}}
    <div
        class="rounded-box border border-primary/20 bg-linear-to-br from-primary/10 via-base-100 to-base-100 p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <div class="rounded-box border border-primary/20 bg-primary/15 p-2.5 text-primary">
                <x-heroicon-o-document-plus class="h-6 w-6" />
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Nuevo Expediente</h1>
                <p class="text-sm text-base-content/70">Registra un nuevo expediente en el sistema.</p>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card border border-base-content/10 bg-base-100 shadow-sm">
        <div class="card-body">
            <livewire:forms.expediente-form />
        </div>
    </div>
</div>
