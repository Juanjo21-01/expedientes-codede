<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Models\Expediente;
use App\Models\Role;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    #[Reactive]
    public $search = '';

    #[Reactive]
    public $estadoFiltro = '';

    #[Reactive]
    public $municipioFiltro = '';

    #[Reactive]
    public $tipoFiltro = '';

    #[Reactive]
    public $anioFiltro = '';

    public int $perPage = 15;

    #[Computed]
    public function expedientes()
    {
        $user = auth()->user();

        return Expediente::query()
            ->accesiblesPor($user)
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->estadoFiltro, fn($q) => $q->deEstado($this->estadoFiltro))
            ->when($this->municipioFiltro, fn($q) => $q->deMunicipio($this->municipioFiltro))
            ->when($this->tipoFiltro, fn($q) => $q->deTipo($this->tipoFiltro))
            ->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))
            ->with(['municipio', 'responsable'])
            ->orderByDesc('fecha_recibido')
            ->paginate($this->perPage);
    }

    #[On('expediente-guardado')]
    #[On('expediente-eliminado')]
    #[On('expediente-estado-cambiado')]
    public function refrescar()
    {
        unset($this->expedientes);
    }

    // Emitir eventos para modales
    public function abrirModalEstado($id)
    {
        $this->dispatch('abrir-modal-estado', expedienteId: $id);
    }

    public function abrirModalEliminar($id)
    {
        $this->dispatch('abrir-modal-eliminar', expedienteId: $id);
    }

    public function abrirModalEnviarRevision($id)
    {
        $this->dispatch('abrir-modal-enviar-revision', expedienteId: $id);
    }

    // Reset página cuando cambian filtros
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedEstadoFiltro()
    {
        $this->resetPage();
    }
    public function updatedMunicipioFiltro()
    {
        $this->resetPage();
    }
    public function updatedTipoFiltro()
    {
        $this->resetPage();
    }
    public function updatedAnioFiltro()
    {
        $this->resetPage();
    }
};
?>

<div>
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
        <table class="table table-zebra table-sm">
            <thead>
                <tr class="bg-base-200 text-xs uppercase tracking-wide">
                    <th class="text-center w-10">No.</th>
                    <th class="whitespace-nowrap">Código SNIP</th>
                    <th>Proyecto</th>
                    <th class="hidden lg:table-cell">Municipio</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center hidden xl:table-cell whitespace-nowrap">Fecha Recibido</th>
                    <th class="hidden xl:table-cell">Responsable</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->expedientes as $index => $expediente)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td class="text-center font-medium text-base-content/50">
                            {{ $this->expedientes->firstItem() + $index }}
                        </td>
                        <td>
                            <span class="font-mono font-bold text-sm">{{ $expediente->codigo_snip }}</span>
                        </td>
                        <td>
                            <div>
                                <p class="font-medium text-sm line-clamp-1" title="{{ $expediente->nombre_proyecto }}">
                                    {{ $expediente->nombre_proyecto }}
                                </p>
                                <div class="flex items-center gap-1.5 text-xs text-base-content/50 mt-0.5">
                                    @if ($expediente->monto_contrato)
                                        <span>{{ $expediente->monto_formateado }}</span>
                                    @endif
                                    <span class="lg:hidden">
                                        @if ($expediente->monto_contrato)
                                            ·
                                        @endif
                                        {{ $expediente->municipio->nombre }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="hidden lg:table-cell">
                            <div class="text-sm">{{ $expediente->municipio->nombre }}</div>
                            <div class="text-xs text-base-content/50">{{ $expediente->municipio->departamento }}</div>
                        </td>
                        <td class="text-center">
                            <span
                                class="badge badge-sm badge-soft {{ $expediente->estado_badge_class }} whitespace-nowrap">
                                {{ $expediente->estado }}
                            </span>
                        </td>
                        <td class="text-center text-sm hidden xl:table-cell whitespace-nowrap">
                            {{ $expediente->fecha_recibido->format('d/m/Y') }}
                        </td>
                        <td class="hidden xl:table-cell">
                            <div class="text-sm">{{ $expediente->responsable->nombre_completo }}</div>
                        </td>
                        <td>
                            <div class="flex justify-center items-center gap-0.5 flex-nowrap">
                                {{-- Ver --}}
                                <div class="tooltip" data-tip="Ver detalle">
                                    <a href="{{ route('expedientes.show', $expediente->id) }}"
                                        class="btn btn-ghost btn-xs btn-square text-info" wire:navigate>
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                </div>

                                {{-- Editar --}}
                                @can('update', $expediente)
                                    <div class="tooltip" data-tip="Editar">
                                        <a href="{{ route('expedientes.edit', $expediente->id) }}"
                                            class="btn btn-ghost btn-xs btn-square text-warning" wire:navigate>
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </a>
                                    </div>
                                @endcan

                                {{-- Enviar a revisión --}}
                                @can('enviarRevision', $expediente)
                                    <div class="tooltip" data-tip="Enviar a revisión">
                                        <button
                                            @click="$dispatch('abrir-modal-enviar-revision', {expedienteId: {{ $expediente->id }} })"
                                            class="btn btn-ghost btn-xs btn-square text-primary">
                                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endcan

                                {{-- Revisión financiera --}}
                                @can('revisarFinanciera', $expediente)
                                    <div class="tooltip" data-tip="Revisar">
                                        <a href="{{ route('expedientes.revision', $expediente->id) }}"
                                            class="btn btn-ghost btn-xs btn-square text-accent" wire:navigate>
                                            <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                                        </a>
                                    </div>
                                @endcan

                                {{-- Cambiar estado --}}
                                @can('cambiarEstado', $expediente)
                                    <div class="tooltip" data-tip="Cambiar estado">
                                        <button
                                            @click="$dispatch('abrir-modal-estado',  {expedienteId: {{ $expediente->id }} })"
                                            class="btn btn-ghost btn-xs btn-square text-secondary">
                                            <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endcan

                                {{-- Eliminar/Archivar --}}
                                @if (auth()->user()->isAdmin())
                                    @if ($expediente->estaRecibido() || $expediente->estaRechazado())
                                        <div class="tooltip"
                                            data-tip="{{ $expediente->estaRecibido() && $expediente->revisionesFinancieras->isEmpty() ? 'Eliminar' : 'Archivar' }}">
                                            <button
                                                @click="$dispatch('abrir-modal-eliminar',  {expedienteId: {{ $expediente->id }} })"
                                                class="btn btn-ghost btn-xs btn-square text-error">
                                                <x-heroicon-o-archive-box-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center gap-3">
                                <div class="bg-base-200 rounded-full p-4">
                                    <x-heroicon-o-folder class="w-10 h-10 text-base-content/20" />
                                </div>
                                <span class="text-base-content/50">No se encontraron expedientes</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->expedientes->hasPages())
            <div class="border-t border-base-content/5 px-4 py-3">
                {{ $this->expedientes->links() }}
            </div>
        @endif
    </div>
</div>
