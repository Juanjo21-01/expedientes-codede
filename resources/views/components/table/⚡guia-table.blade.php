<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Guia;

new class extends Component {
    use WithPagination;

    #[Reactive]
    public string $search = '';

    #[Reactive]
    public string $categoriaFiltro = '';

    #[Reactive]
    public string $estadoFiltro = '';

    // Resetear paginación al cambiar filtros
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoriaFiltro()
    {
        $this->resetPage();
    }

    public function updatedEstadoFiltro()
    {
        $this->resetPage();
    }

    #[Computed]
    public function guias()
    {
        $query = Guia::query()->orderByDesc('created_at');

        if ($this->search) {
            $query->buscar($this->search);
        }

        if ($this->categoriaFiltro) {
            $query->deCategoria($this->categoriaFiltro);
        }

        if ($this->estadoFiltro === 'activo') {
            $query->activas();
        } elseif ($this->estadoFiltro === 'inactivo') {
            $query->inactivas();
        }

        return $query->paginate(10);
    }

    public function abrirPdfModal(int $id)
    {
        $this->dispatch('abrir-pdf-modal', guiaId: $id);
    }

    public function abrirEstadoModal(int $id)
    {
        $this->dispatch('abrir-estado-modal', guiaId: $id);
    }

    public function abrirDeleteModal(int $id)
    {
        $this->dispatch('abrir-delete-modal', guiaId: $id);
    }

    #[On('guia-eliminada')]
    #[On('guia-estado-cambiado')]
    public function refrescar()
    {
        unset($this->guias);
    }
};
?>

<div>
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
        <table class="table table-zebra table-sm">
            <thead>
                <tr class="bg-base-200">
                    <th class="min-w-[200px]">Título</th>
                    <th class="min-w-[150px]">Categoría</th>
                    <th class="text-center whitespace-nowrap">Versión</th>
                    <th class="whitespace-nowrap">Fecha</th>
                    <th class="text-center whitespace-nowrap">Tamaño</th>
                    <th class="text-center whitespace-nowrap">Estado</th>
                    <th class="text-center whitespace-nowrap min-w-[150px]">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->guias as $guia)
                    <tr wire:key="guia-{{ $guia->id }}" class="hover">
                        <td>
                            <div class="font-medium">{{ $guia->titulo }}</div>
                        </td>
                        <td>
                            <span class="badge badge-outline badge-sm">{{ $guia->categoria }}</span>
                        </td>
                        <td class="text-center">
                            <span class="font-mono text-sm">v{{ $guia->version }}</span>
                        </td>
                        <td class="text-sm text-base-content/70">
                            {{ $guia->fecha_publicacion->format('d/m/Y') }}
                        </td>
                        <td class="text-center text-sm text-base-content/70">
                            {{ $guia->tamanio_archivo }}
                        </td>
                        <td class="text-center">
                            @if ($guia->estado)
                                <span class="badge badge-success badge-sm gap-1">
                                    <x-heroicon-o-check class="w-3 h-3" />
                                    Activo
                                </span>
                            @else
                                <span class="badge badge-ghost badge-sm">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex justify-center items-center gap-1">
                                {{-- Ver PDF --}}
                                <div class="tooltip" data-tip="Ver PDF">
                                    <button wire:click="abrirPdfModal({{ $guia->id }})"
                                        class="btn btn-ghost btn-sm btn-square text-info">
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </button>
                                </div>

                                {{-- Editar (solo Admin) --}}
                                @can('update', $guia)
                                    <div class="tooltip" data-tip="Editar">
                                        <a href="{{ route('admin.guias.edit', $guia) }}" wire:navigate
                                            class="btn btn-ghost btn-sm btn-square text-warning">
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        </a>
                                    </div>
                                @endcan

                                {{-- Toggle Estado (solo Admin) --}}
                                @can('toggleEstado', $guia)
                                    <div class="tooltip" data-tip="{{ $guia->estado ? 'Desactivar' : 'Activar' }}">
                                        <button wire:click="abrirEstadoModal({{ $guia->id }})"
                                            class="btn btn-ghost btn-sm btn-square">
                                            @if ($guia->estado)
                                                <x-heroicon-o-eye-slash class="w-5 h-5 text-warning" />
                                            @else
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
                                            @endif
                                        </button>
                                    </div>
                                @endcan

                                {{-- Eliminar (solo Admin) --}}
                                @can('delete', $guia)
                                    <div class="tooltip" data-tip="Eliminar">
                                        <button wire:click="abrirDeleteModal({{ $guia->id }})"
                                            class="btn btn-ghost btn-sm btn-square text-error">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2">
                                <x-heroicon-o-document class="w-12 h-12 text-base-content/30" />
                                <span class="text-base-content/50">No se encontraron guías</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if ($this->guias->hasPages())
        <div class="border-t border-base-content/5 px-4 py-3">
            {{ $this->guias->links() }}
        </div>
    @endif
</div>
