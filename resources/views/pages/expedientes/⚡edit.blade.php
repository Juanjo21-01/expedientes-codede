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
                <a href="{{ route('expedientes.show', $expediente->id) }}" wire:navigate
                    class="font-medium text-base-content/60 hover:text-primary">
                    <span class="font-mono">{{ $expediente->codigo_snip }}</span>
                </a>
            </li>
            <li><span class="font-medium text-primary">Editar</span></li>
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
                <h1 class="text-2xl font-bold tracking-tight">Editar Expediente</h1>
                <p class="text-sm text-base-content/70">
                    <span class="font-mono">{{ $expediente->codigo_snip }}</span> ·
                    {{ $expediente->nombre_proyecto }}
                </p>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card border border-base-content/10 bg-base-100 shadow-sm">
        <div class="card-body">
            <livewire:forms.expediente-form :expedienteId="$expediente->id" />
        </div>
    </div>
</div>
