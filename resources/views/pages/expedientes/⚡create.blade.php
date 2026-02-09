<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('- Nuevo Expediente')] class extends Component {
    //
};
?>

<div>
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm mb-6">
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
    <div class="flex items-center gap-3 mb-6">
        <div class="bg-primary/10 text-primary rounded-btn p-2">
            <x-heroicon-o-document-plus class="w-6 h-6" />
        </div>
        <div>
            <h1 class="text-2xl font-bold">Nuevo Expediente</h1>
            <p class="text-base-content/60 text-sm">Registra un nuevo expediente en el sistema</p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card bg-base-100 shadow-sm border border-base-300">
        <div class="card-body">
            <livewire:forms.expediente-form />
        </div>
    </div>
</div>
