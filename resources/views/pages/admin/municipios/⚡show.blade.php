<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Municipio;
use App\Models\Expediente;
use App\Models\Role;
use Carbon\Carbon;

new #[Title('- Detalle Municipio')] class extends Component {
    public Municipio $municipio;

    // Variables para mensajes
    public string $mensajeTipo = '';
    public string $mensajeTexto = '';

    // Filtro de año para expedientes
    public string $anioFiltro = '';

    // Montar el componente
    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio->load(['expedientes.tipoSolicitud', 'expedientes.responsable']);
        $this->anioFiltro = (string) now()->year;
    }

    // Escuchar mensaje para mostrar
    #[On('mostrar-mensaje')]
    public function mostrarMensaje($tipo, $mensaje)
    {
        $this->mensajeTipo = $tipo;
        $this->mensajeTexto = $mensaje;
    }

    // Cerrar mensaje
    public function cerrarMensaje()
    {
        $this->mensajeTipo = '';
        $this->mensajeTexto = '';
    }

    // Refrescar cuando se edita el municipio
    #[On('municipio-guardado')]
    public function refrescar()
    {
        $this->municipio = $this->municipio->fresh(['expedientes.tipoSolicitud', 'expedientes.responsable']);
    }

    // Emitir evento para editar (solo Admin)
    public function editar()
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->dispatch('abrir-modal-municipio', municipioId: $this->municipio->id);
    }

    // Cambiar estado del municipio (solo Admin)
    public function cambiarEstado()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($this->municipio->estaActivo()) {
            $this->municipio->desactivar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Municipio desactivado correctamente.');
        } else {
            $this->municipio->activar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Municipio activado correctamente.');
        }
        $this->municipio = $this->municipio->fresh();
    }

    // Años disponibles para filtro
    #[Computed]
    public function aniosDisponibles()
    {
        $anios = $this->municipio->expedientes()->selectRaw('YEAR(fecha_recibido) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->toArray();

        // Si no hay expedientes, mostrar al menos el año actual
        if (empty($anios)) {
            $anios = [now()->year];
        }

        return $anios;
    }

    // Usuario Municipal asignado
    #[Computed]
    public function usuarioMunicipal()
    {
        return $this->municipio->users()->whereHas('role', fn($q) => $q->where('nombre', Role::MUNICIPAL))->where('users.estado', true)->first();
    }

    // Técnicos asignados
    #[Computed]
    public function tecnicosAsignados()
    {
        return $this->municipio->users()->whereHas('role', fn($q) => $q->where('nombre', Role::TECNICO))->where('users.estado', true)->get();
    }

    // Estadísticas generales del municipio
    #[Computed]
    public function estadisticas()
    {
        $expedientes = $this->municipio->expedientes;

        return [
            'total' => $expedientes->count(),
            'recibidos' => $expedientes->where('estado', Expediente::ESTADO_RECIBIDO)->count(),
            'en_revision' => $expedientes->where('estado', Expediente::ESTADO_EN_REVISION)->count(),
            'completos' => $expedientes->where('estado', Expediente::ESTADO_COMPLETO)->count(),
            'incompletos' => $expedientes->where('estado', Expediente::ESTADO_INCOMPLETO)->count(),
            'aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
            'archivados' => $expedientes->where('estado', Expediente::ESTADO_ARCHIVADO)->count(),
        ];
    }

    // Estadísticas filtradas por año
    #[Computed]
    public function estadisticasAnio()
    {
        $expedientes = $this->municipio->expedientes()->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))->get();

        return [
            'total' => $expedientes->count(),
            'recibidos' => $expedientes->where('estado', Expediente::ESTADO_RECIBIDO)->count(),
            'en_revision' => $expedientes->where('estado', Expediente::ESTADO_EN_REVISION)->count(),
            'completos' => $expedientes->where('estado', Expediente::ESTADO_COMPLETO)->count(),
            'incompletos' => $expedientes->where('estado', Expediente::ESTADO_INCOMPLETO)->count(),
            'aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
            'archivados' => $expedientes->where('estado', Expediente::ESTADO_ARCHIVADO)->count(),
        ];
    }

    // Expedientes del año seleccionado
    #[Computed]
    public function expedientesAnio()
    {
        return $this->municipio
            ->expedientes()
            ->when($this->anioFiltro, fn($q) => $q->whereYear('fecha_recibido', $this->anioFiltro))
            ->with(['tipoSolicitud', 'responsable'])
            ->orderBy('fecha_recibido', 'desc')
            ->get();
    }

    // Datos para la gráfica de expedientes por mes del año seleccionado
    #[Computed]
    public function chartData()
    {
        $anio = $this->anioFiltro ?: now()->year;

        $expedientesPorMes = $this->municipio->expedientes()->whereYear('fecha_recibido', $anio)->selectRaw('MONTH(fecha_recibido) as mes, COUNT(*) as cantidad')->groupBy('mes')->pluck('cantidad', 'mes')->toArray();

        $labels = [];
        $data = [];
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $meses[$i - 1];
            $data[] = $expedientesPorMes[$i] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    // Datos para gráfica de estados (pie chart)
    #[Computed]
    public function chartEstados()
    {
        $stats = $this->estadisticasAnio;
        return [
            'labels' => ['Recibidos', 'En Revisión', 'Completos', 'Incompletos', 'Aprobados', 'Rechazados', 'Archivados'],
            'data' => [$stats['recibidos'], $stats['en_revision'], $stats['completos'], $stats['incompletos'], $stats['aprobados'], $stats['rechazados'], $stats['archivados']],
            'colors' => [
                'rgba(59, 130, 246, 0.7)', // info - Recibidos
                'rgba(234, 179, 8, 0.7)', // warning - En Revisión
                'rgba(34, 197, 94, 0.7)', // success - Completos
                'rgba(249, 115, 22, 0.7)', // orange - Incompletos
                'rgba(16, 185, 129, 0.7)', // emerald - Aprobados
                'rgba(239, 68, 68, 0.7)', // error - Rechazados
                'rgba(148, 163, 184, 0.7)', // slate - Archivados
            ],
        ];
    }

    // Reset de computadas cuando cambia el año
    public function updatedAnioFiltro()
    {
        unset($this->estadisticasAnio);
        unset($this->expedientesAnio);
        unset($this->chartData);
        unset($this->chartEstados);
    }
};
?>

<div class="space-y-6">
    {{-- Mensajes Flash --}}
    @if ($mensajeTexto)
        <div role="alert" class="alert alert-{{ $mensajeTipo }} shadow-lg">
            @if ($mensajeTipo === 'success')
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif ($mensajeTipo === 'warning')
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
            <span>{{ $mensajeTexto }}</span>
            <button type="button" wire:click="cerrarMensaje" class="btn btn-sm btn-circle btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Breadcrumbs --}}
    <div class="breadcrumbs text-sm">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate class="gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Inicio
                </a>
            </li>
            <li>
                <a href="{{ route('admin.municipios.index') }}" wire:navigate class="gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 21h19.5M3.75 3v18m16.5-18v18M5.25 3h13.5M5.25 21h13.5M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    Municipios
                </a>
            </li>
            <li>
                <span class="inline-flex items-center gap-1 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    {{ $municipio->nombre }}
                </span>
            </li>
        </ul>
    </div>

    {{-- Tarjeta Principal del Municipio --}}
    <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-xl border border-base-300 overflow-hidden">
        {{-- Header decorativo --}}
        <div class="h-20 bg-gradient-to-r from-primary/20 via-accent/20 to-secondary/20"></div>

        <div class="card-body -mt-12">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Ícono del municipio --}}
                <div class="indicator">
                    <span
                        class="indicator-item indicator-bottom indicator-end badge {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }} badge-sm"></span>
                    <div
                        class="bg-primary text-primary-content rounded-full w-24 h-24 ring-4 ring-base-100 shadow-lg flex justify-center items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 21h19.5M3.75 3v18m16.5-18v18M5.25 3h13.5M5.25 21h13.5M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                </div>

                {{-- Información principal --}}
                <div class="flex-1 text-center sm:text-left">
                    <h1 class="text-3xl font-bold tracking-tight">{{ $municipio->nombre }}</h1>
                    <p class="text-base-content/70 text-lg">{{ $municipio->departamento }}</p>

                    <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                        <div
                            class="badge badge-lg gap-1 {{ $municipio->estaActivo() ? 'badge-success' : 'badge-error' }}">
                            @if ($municipio->estaActivo())
                                <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                            @endif
                            {{ $municipio->estaActivo() ? 'Activo' : 'Inactivo' }}
                        </div>
                        @if ($municipio->tieneContactoCompleto())
                            <div class="badge badge-lg badge-outline badge-info gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Contacto completo
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Acciones (solo Admin) --}}
                @if (auth()->user()->isAdmin())
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button wire:click="cambiarEstado"
                            class="btn {{ $municipio->estaActivo() ? 'btn-outline btn-error' : 'btn-success' }} btn-sm gap-2">
                            @if ($municipio->estaActivo())
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Desactivar
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Activar
                            @endif
                        </button>
                        <button wire:click="editar" class="btn btn-primary btn-sm gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                            Editar contacto
                        </button>
                    </div>
                @endif
            </div>

            <div class="divider my-4"></div>

            {{-- Detalles en grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Contacto --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 text-primary rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Contacto</p>
                            <p class="font-semibold">{{ $municipio->contacto_nombre ?? 'Sin asignar' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Email --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-info/10 text-info rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Email</p>
                            <p class="font-semibold text-sm">{{ $municipio->contacto_email ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Teléfono --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-success/10 text-success rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Teléfono</p>
                            <p class="font-semibold">{{ $municipio->contacto_telefono ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-warning/10 text-warning rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Observaciones</p>
                            <p class="font-semibold text-sm truncate">
                                {{ $municipio->observaciones ?? 'Sin observaciones' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Usuarios Asignados --}}
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-lg gap-3">
                <div class="bg-accent/10 text-accent rounded-btn p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                Usuarios Asignados
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                {{-- Usuario Municipal --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-ghost badge-sm">Municipal</span>
                    </div>
                    @if ($this->usuarioMunicipal)
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-neutral text-neutral-content rounded-full w-10 h-10 flex items-center justify-center">
                                    <span class="text-sm">{{ $this->usuarioMunicipal->iniciales }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold truncate">{{ $this->usuarioMunicipal->nombre_completo }}</p>
                                <p class="text-sm text-base-content/60 truncate">{{ $this->usuarioMunicipal->email }}
                                </p>
                                @if ($this->usuarioMunicipal->telefono)
                                    <p class="text-xs text-base-content/50">Tel:
                                        {{ $this->usuarioMunicipal->telefono }}</p>
                                @endif
                            </div>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.usuarios.show', $this->usuarioMunicipal->id) }}"
                                    wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Ver perfil">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="text-base-content/40 text-sm italic flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Sin usuario municipal asignado
                        </div>
                    @endif
                </div>

                {{-- Técnicos Asignados --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-info badge-sm">Técnico(s)</span>
                        <span class="badge badge-ghost badge-xs">{{ $this->tecnicosAsignados->count() }}</span>
                    </div>
                    @if ($this->tecnicosAsignados->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($this->tecnicosAsignados as $tecnico)
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div
                                            class="bg-info text-info-content rounded-full w-10 h-10 flex items-center justify-center">
                                            <span class="text-sm">{{ $tecnico->iniciales }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold truncate">{{ $tecnico->nombre_completo }}</p>
                                        <p class="text-sm text-base-content/60 truncate">{{ $tecnico->email }}</p>
                                    </div>
                                    @if (auth()->user()->isAdmin())
                                        <a href="{{ route('admin.usuarios.show', $tecnico->id) }}" wire:navigate
                                            class="btn btn-ghost btn-sm btn-circle" title="Ver perfil">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-base-content/40 text-sm italic flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Sin técnicos asignados
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas Generales --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Expedientes --}}
        <div
            class="stat bg-gradient-to-br from-primary/5 to-primary/10 rounded-box shadow border border-primary/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-primary opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-10 h-10">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>
            </div>
            <div class="stat-title text-primary/70">Total Expedientes</div>
            <div class="stat-value text-primary">{{ $this->estadisticas['total'] }}</div>
            <div class="stat-desc">Histórico</div>
        </div>

        {{-- Aprobados --}}
        <div
            class="stat bg-gradient-to-br from-success/5 to-success/10 rounded-box shadow border border-success/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-success opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-10 h-10">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="stat-title text-success/70">Aprobados</div>
            <div class="stat-value text-success">{{ $this->estadisticas['aprobados'] }}</div>
            <div class="stat-desc">Finalizados</div>
        </div>

        {{-- En proceso --}}
        <div
            class="stat bg-gradient-to-br from-warning/5 to-warning/10 rounded-box shadow border border-warning/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-warning opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-10 h-10">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="stat-title text-warning/70">En Proceso</div>
            <div class="stat-value text-warning">
                {{ $this->estadisticas['recibidos'] + $this->estadisticas['en_revision'] + $this->estadisticas['completos'] + $this->estadisticas['incompletos'] }}
            </div>
            <div class="stat-desc">Activos</div>
        </div>

        {{-- Rechazados --}}
        <div
            class="stat bg-gradient-to-br from-error/5 to-error/10 rounded-box shadow border border-error/20 hover:shadow-lg transition-shadow">
            <div class="stat-figure text-error opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-10 h-10">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="stat-title text-error/70">Rechazados</div>
            <div class="stat-value text-error">{{ $this->estadisticas['rechazados'] }}</div>
            <div class="stat-desc">No aprobados</div>
        </div>
    </div>

    {{-- Sección por Año con Filtro --}}
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <h2 class="card-title text-lg gap-3">
                    <div class="bg-secondary/10 text-secondary rounded-btn p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    Expedientes por Año
                </h2>

                <select wire:model.live="anioFiltro"
                    class="select select-bordered select-sm focus:select-primary w-32">
                    @foreach ($this->aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Badges de estados del año --}}
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="badge badge-info gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['recibidos'] }}</span> Recibidos
                </span>
                <span class="badge badge-warning gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['en_revision'] }}</span> En Revisión
                </span>
                <span class="badge badge-success badge-outline gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['completos'] }}</span> Completos
                </span>
                <span class="badge badge-warning badge-outline gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['incompletos'] }}</span> Incompletos
                </span>
                <span class="badge badge-success gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['aprobados'] }}</span> Aprobados
                </span>
                <span class="badge badge-error gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['rechazados'] }}</span> Rechazados
                </span>
                <span class="badge badge-ghost gap-1">
                    <span class="font-bold">{{ $this->estadisticasAnio['archivados'] }}</span> Archivados
                </span>
            </div>

            {{-- Gráficas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                {{-- Gráfica de barras por mes --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <h3 class="font-semibold text-sm mb-3 text-base-content/70">Expedientes por Mes —
                        {{ $anioFiltro }}</h3>
                    <div class="w-full h-64" wire:ignore x-data="barChart(@js($this->chartData))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartData))">
                        <canvas x-ref="barChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                {{-- Gráfica de pie por estados --}}
                <div class="bg-base-200/30 rounded-box p-4 border border-base-300">
                    <h3 class="font-semibold text-sm mb-3 text-base-content/70">Distribución por Estado —
                        {{ $anioFiltro }}</h3>
                    <div class="w-full h-64" wire:ignore x-data="pieChart(@js($this->chartEstados))" x-init="initChart()"
                        x-effect="updateChart(@js($this->chartEstados))">
                        <canvas x-ref="pieChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Expedientes del Año --}}
    @if ($this->expedientesAnio->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="card-title text-lg gap-3">
                        <div class="bg-info/10 text-info rounded-btn p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        Expedientes {{ $anioFiltro }}
                        <span class="badge badge-neutral badge-sm">{{ $this->expedientesAnio->count() }}</span>
                    </h2>
                </div>

                <div class="overflow-x-auto mt-3">
                    <table class="table table-zebra table-pin-rows">
                        <thead>
                            <tr class="bg-base-200">
                                <th class="text-center">No.</th>
                                <th>Código SNIP</th>
                                <th>Proyecto</th>
                                <th>Tipo Solicitud</th>
                                <th>Tipo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Fecha Recibido</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->expedientesAnio as $index => $expediente)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="text-center font-medium">{{ $index + 1 }}</td>
                                    <td>
                                        <span
                                            class="font-mono font-bold text-primary">{{ $expediente->codigo_snip }}</span>
                                    </td>
                                    <td>
                                        <div class="tooltip tooltip-right"
                                            data-tip="{{ $expediente->nombre_proyecto }}">
                                            <div class="max-w-xs truncate font-medium">
                                                {{ $expediente->nombre_proyecto }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="text-sm text-base-content/70">{{ $expediente->tipoSolicitud->nombre ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-outline badge-xs">{{ $expediente->ordinario_extraordinario ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge badge-sm gap-1
                                            @if ($expediente->estado === 'Aprobado') badge-success
                                            @elseif($expediente->estado === 'Rechazado') badge-error
                                            @elseif($expediente->estado === 'En Revisión') badge-warning
                                            @elseif($expediente->estado === 'Recibido') badge-info
                                            @elseif($expediente->estado === 'Completo') badge-success badge-outline
                                            @elseif($expediente->estado === 'Incompleto') badge-warning badge-outline
                                            @elseif($expediente->estado === 'Archivado') badge-ghost
                                            @else badge-ghost @endif">
                                            @if ($expediente->estado === 'Aprobado')
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            @elseif($expediente->estado === 'Rechazado')
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                            {{ $expediente->estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="text-sm text-base-content/60">{{ $expediente->fecha_recibido?->format('d/m/Y') ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if ($expediente->responsable)
                                            <span
                                                class="text-sm">{{ $expediente->responsable->nombre_completo }}</span>
                                        @else
                                            <span class="text-base-content/40">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <div class="flex flex-col items-center justify-center py-8 gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-16 h-16 text-base-content/20">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                    <p class="text-base-content/50 text-lg">No hay expedientes para el año {{ $anioFiltro }}</p>
                    <p class="text-base-content/40 text-sm">Selecciona otro año o verifica que existan expedientes
                        registrados.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Editar Municipio (solo Admin) --}}
    @if (auth()->user()->isAdmin())
        <livewire:modals.municipio-modal />
    @endif
</div>

{{-- Scripts para Chart.js --}}
@script
    <script>
        Alpine.data('barChart', (initialData) => ({
            chart: null,
            initChart() {
                const ctx = this.$refs.barChart.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: initialData.labels,
                        datasets: [{
                            label: 'Expedientes',
                            data: initialData.data,
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(59, 130, 246, 0.8)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: (ctx) =>
                                        `${ctx.parsed.y} expediente${ctx.parsed.y !== 1 ? 's' : ''}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            },
            updateChart(newData) {
                if (this.chart) {
                    this.chart.data.labels = newData.labels;
                    this.chart.data.datasets[0].data = newData.data;
                    this.chart.update('active');
                }
            }
        }));

        Alpine.data('pieChart', (initialData) => ({
            chart: null,
            initChart() {
                const ctx = this.$refs.pieChart.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: initialData.labels,
                        datasets: [{
                            data: initialData.data,
                            backgroundColor: initialData.colors,
                            borderWidth: 2,
                            borderColor: 'rgba(255, 255, 255, 0.8)',
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 11
                                    },
                                    padding: 12,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) =>
                                        ` ${ctx.label}: ${ctx.parsed} expediente${ctx.parsed !== 1 ? 's' : ''}`
                                }
                            }
                        },
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            },
            updateChart(newData) {
                if (this.chart) {
                    this.chart.data.labels = newData.labels;
                    this.chart.data.datasets[0].data = newData.data;
                    this.chart.data.datasets[0].backgroundColor = newData.colors;
                    this.chart.update('active');
                }
            }
        }));
    </script>
@endscript
