<?php

use App\Models\Bitacora;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Reactive]
    public string $search = '';

    #[Reactive]
    public string $entidad = '';

    #[Reactive]
    public string $tipo = '';

    #[Reactive]
    public string $usuario_id = '';

    #[Reactive]
    public string $fecha_desde = '';

    #[Reactive]
    public string $fecha_hasta = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEntidad(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedUsuarioId(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage();
    }

    private function baseQuery()
    {
        return Bitacora::query()
            ->with('user')
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->entidad, fn($q) => $q->deEntidad($this->entidad))
            ->when($this->tipo, fn($q) => $q->deTipo($this->tipo))
            ->when($this->usuario_id, fn($q) => $q->deUsuario((int) $this->usuario_id))
            ->when($this->fecha_desde && $this->fecha_hasta, fn($q) => $q->entreFechas($this->fecha_desde, $this->fecha_hasta))
            ->recientes();
    }

    #[Computed]
    public function registros()
    {
        return $this->baseQuery()->paginate(20);
    }
};
?>

<div>
    <div class="bg-base-100 rounded-box shadow-sm border border-base-300 overflow-x-auto">
        <table class="table table-zebra table-sm">
            <thead>
                <tr class="bg-base-200/50">
                    <th class="w-14">No. </th>
                    <th>Fecha / Hora</th>
                    <th>Usuario</th>
                    <th>Entidad</th>
                    <th>Tipo</th>
                    <th>Detalle</th>
                    <th class="w-20 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->registros as $registro)
                    <tr wire:key="bitacora-{{ $registro->id }}" class="hover">
                        <td class="text-xs text-base-content/50">{{ $registro->id }}</td>
                        <td class="whitespace-nowrap">
                            <div class="text-sm font-medium">{{ $registro->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-base-content/50">{{ $registro->created_at->format('H:i:s') }}
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div
                                        class="bg-neutral text-neutral-content rounded-full w-7 h-7 flex items-center justify-center">
                                        <span class="text-xs">{{ $registro->user?->iniciales ?? 'S' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-medium">
                                        {{ $registro->user?->nombre_completo ?? 'Sistema' }}</div>
                                    <div class="text-xs text-base-content/50">
                                        {{ $registro->user?->role?->nombre ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-sm {{ $registro->entidad_badge_class }} badge-outline">
                                {{ $registro->entidad }}
                            </span>
                            @if ($registro->entidad_id)
                                <span class="text-xs text-base-content/40 ml-1">#{{ $registro->entidad_id }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-sm {{ $registro->tipo_badge_class }}">
                                {{ $registro->tipo }}
                            </span>
                        </td>
                        <td>
                            <p class="text-sm max-w-xs truncate" title="{{ $registro->detalle }}">
                                {{ $registro->detalle }}
                            </p>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                @click="$dispatch('ver-detalle-bitacora', { registroId: {{ $registro->id }} })"
                                class="btn btn-ghost btn-xs btn-circle tooltip" data-tip="Ver detalle">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2 text-base-content/40">
                                <x-heroicon-o-clipboard-document-list class="w-12 h-12" />
                                <p class="font-medium">No se encontraron registros</p>
                                <p class="text-sm">Ajusta los filtros o el rango de fechas</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->registros->hasPages())
        <div class="mt-4">
            {{ $this->registros->links() }}
        </div>
    @endif
</div>
