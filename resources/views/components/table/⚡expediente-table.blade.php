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
    public $tipoSolicitudFiltro = '';

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
            ->when($this->tipoSolicitudFiltro, fn($q) => $q->where('tipo_solicitud_id', $this->tipoSolicitudFiltro))
            ->when($this->tipoFiltro, fn($q) => $q->deTipo($this->tipoFiltro))
            ->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))
            ->with(['municipio', 'responsable', 'tipoSolicitud'])
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

    public function enviarARevision($id)
    {
        $expediente = Expediente::find($id);
        if (!$expediente) {
            return;
        }

        $user = auth()->user();
        if (!$user->can('enviarRevision', $expediente)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No tienes permiso para enviar a revisión.');
            return;
        }

        $expediente->marcarEnRevision();
        unset($this->expedientes);
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Expediente '{$expediente->codigo_snip}' enviado a revisión.");
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
    public function updatedTipoSolicitudFiltro()
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
                <tr class="bg-base-200">
                    <th class="text-center w-12">No.</th>
                    <th class="min-w-28 whitespace-nowrap">Código SNIP</th>
                    <th class="min-w-48">Proyecto</th>
                    <th class="min-w-32 whitespace-nowrap">Municipio</th>
                    <th class="text-center min-w-24 whitespace-nowrap">Estado</th>
                    <th class="text-center min-w-28 whitespace-nowrap">Fecha Recibido</th>
                    <th class="min-w-32 whitespace-nowrap">Responsable</th>
                    <th class="text-center min-w-36 whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->expedientes as $index => $expediente)
                    <tr class="hover">
                        <td class="text-center font-medium">
                            {{ $this->expedientes->firstItem() + $index }}
                        </td>
                        <td>
                            <span class="font-mono font-bold text-sm">{{ $expediente->codigo_snip }}</span>
                        </td>
                        <td>
                            <div class="max-w-xs">
                                <div class="font-medium text-sm truncate" title="{{ $expediente->nombre_proyecto }}">
                                    {{ Str::limit($expediente->nombre_proyecto, 40) }}
                                </div>
                                @if ($expediente->monto_contrato)
                                    <div class="text-xs opacity-60 text-center">{{ $expediente->monto_formateado }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-sm">{{ $expediente->municipio->nombre }}</div>
                            <div class="text-xs opacity-60">{{ $expediente->municipio->departamento }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-sm {{ $expediente->estado_badge_class }} text-nowrap">
                                {{ $expediente->estado }}
                            </span>
                        </td>
                        <td class="text-center text-sm">
                            {{ $expediente->fecha_recibido->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="text-sm">{{ $expediente->responsable->nombre_completo }}</div>
                        </td>
                        <td>
                            <div class="flex justify-center items-center gap-1">
                                {{-- Ver --}}
                                <div class="tooltip" data-tip="Ver detalle">
                                    <a href="{{ route('expedientes.show', $expediente->id) }}"
                                        class="btn btn-ghost btn-sm btn-square text-info" wire:navigate>
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </a>
                                </div>

                                {{-- Editar (Técnico en estados editables, Admin siempre) --}}
                                @can('update', $expediente)
                                    <div class="tooltip" data-tip="Editar">
                                        <a href="{{ route('expedientes.edit', $expediente->id) }}"
                                            class="btn btn-ghost btn-sm btn-square text-warning" wire:navigate>
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        </a>
                                    </div>
                                @endcan

                                {{-- Enviar a revisión (Técnico, Recibido) --}}
                                @can('enviarRevision', $expediente)
                                    <div class="tooltip" data-tip="Enviar a revisión">
                                        <button wire:click="enviarARevision({{ $expediente->id }})"
                                            wire:confirm="¿Enviar este expediente a revisión financiera?"
                                            class="btn btn-ghost btn-sm btn-square text-primary">
                                            <x-heroicon-o-paper-airplane class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endcan

                                {{-- Revisión financiera (Jefe / Admin) --}}
                                @can('revisarFinanciera', $expediente)
                                    <div class="tooltip" data-tip="Revisar">
                                        <a href="{{ route('expedientes.revision', $expediente->id) }}"
                                            class="btn btn-ghost btn-sm btn-square text-accent" wire:navigate>
                                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                                        </a>
                                    </div>
                                @endcan

                                {{-- Cambiar estado (Admin) --}}
                                @can('cambiarEstado', $expediente)
                                    <div class="tooltip" data-tip="Cambiar estado">
                                        <button
                                            @click="$dispatch('abrir-modal-estado',  {expedienteId: {{ $expediente->id }} })"
                                            class="btn btn-ghost btn-sm btn-square text-secondary">
                                            <x-heroicon-o-arrows-right-left class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endcan

                                {{-- Eliminar/Archivar (Admin) --}}
                                @if (auth()->user()->isAdmin())
                                    @if (!$expediente->estaArchivado())
                                        <div class="tooltip"
                                            data-tip="{{ $expediente->estaRecibido() && $expediente->revisionesFinancieras->isEmpty() ? 'Eliminar' : 'Archivar' }}">
                                            <button
                                                @click="$dispatch('abrir-modal-eliminar',  {expedienteId: {{ $expediente->id }} })"
                                                class="btn btn-ghost btn-sm btn-square text-error">
                                                <x-heroicon-o-archive-box-x-mark class="w-5 h-5" />
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2">
                                <x-heroicon-o-folder class="w-12 h-12 text-base-content/30" />
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
