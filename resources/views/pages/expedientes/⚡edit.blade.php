<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Expediente;

new #[Title('- Editar Expediente')] class extends Component {
    public Expediente $expediente;

    public function mount(Expediente $expediente)
    {
        $this->expediente = $expediente;
    }
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
                <a href="{{ route('expedientes.show', $expediente->id) }}" wire:navigate
                    class="font-medium text-base-content/60 hover:text-primary">
                    <span class="font-mono">{{ $expediente->codigo_snip }}</span>
                </a>
            </li>
            <li><span class="font-medium text-primary">Editar</span></li>
        </ul>
    </div>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="avatar placeholder">
            <div class="bg-warning/10 text-warning rounded-lg w-12 h-12 flex items-center justify-center">
                <x-heroicon-o-pencil-square class="w-6 h-6" />
            </div>
        </div>
        <div>
            <h1 class="text-2xl font-bold">Editar Expediente</h1>
            <p class="text-base-content/60 text-sm">
                <span class="font-mono">{{ $expediente->codigo_snip }}</span> ·
                {{ $expediente->nombre_proyecto }}
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card bg-base-100 shadow-sm border border-base-300">
        <div class="card-body">
            <livewire:forms.expediente-form :expedienteId="$expediente->id" />
        </div>
    </div>
</div>
