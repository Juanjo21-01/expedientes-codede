<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\User;
use App\Models\Expediente;
use Carbon\Carbon;

new #[Title('- Detalle Usuario')] class extends Component {
    public User $usuario;

    // Variables para mensajes
    public string $mensajeTipo = '';
    public string $mensajeTexto = '';

    // Montar el componente con el usuario
    public function mount(User $usuario)
    {
        $this->usuario = $usuario->load(['role', 'municipios', 'expedientes.municipio', 'expedientes.tipoSolicitud']);
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

    // Refrescar cuando se edita el usuario
    #[On('usuario-guardado')]
    public function refrescar()
    {
        $this->usuario = $this->usuario->fresh(['role', 'municipios', 'expedientes.municipio', 'expedientes.tipoSolicitud']);
    }

    // Cambiar estado del usuario
    public function cambiarEstado()
    {
        // No permitir cambiar estado del Administrador
        if ($this->usuario->isAdmin()) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No puedes cambiar el estado de un Administrador.');
            return;
        }

        // Cambiar estado
        if ($this->usuario->estaActivo()) {
            $this->usuario->desactivar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Usuario desactivado correctamente.');
        } else {
            $this->usuario->activar();
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Usuario activado correctamente.');
        }

        $this->usuario = $this->usuario->fresh(['role', 'municipios']);
    }

    // Emitir evento para editar
    public function editar()
    {
        $this->dispatch('abrir-modal-usuario', usuarioId: $this->usuario->id);
    }

    // Estadísticas del usuario
    public function getEstadisticasProperty(): array
    {
        $expedientes = $this->usuario->expedientes;

        return [
            'total_expedientes' => $expedientes->count(),
            'expedientes_aprobados' => $expedientes->where('estado', Expediente::ESTADO_APROBADO)->count(),
            'expedientes_pendientes' => $expedientes->whereIn('estado', [Expediente::ESTADO_RECIBIDO, Expediente::ESTADO_EN_REVISION, Expediente::ESTADO_COMPLETO, Expediente::ESTADO_INCOMPLETO])->count(),
            'expedientes_rechazados' => $expedientes->where('estado', Expediente::ESTADO_RECHAZADO)->count(),
        ];
    }

    // Últimos expedientes del usuario
    public function getUltimosExpedientesProperty()
    {
        return $this->usuario
            ->expedientes()
            ->with(['municipio', 'tipoSolicitud'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    // Datos para la gráfica de expedientes por mes
    public function getChartDataProperty(): array
    {
        $expedientesPorMes = $this->usuario
            ->expedientes()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, COUNT(*) as cantidad')
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('mes')
            ->pluck('cantidad', 'mes')
            ->toArray();

        $expedientesData = [];
        $mesesDato = collect();

        for ($i = 5; $i >= 0; $i--) {
            $mesesDato->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        foreach ($mesesDato as $mes) {
            $expedientesData[] = $expedientesPorMes[$mes] ?? 0;
        }

        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $meses->push(Carbon::now()->subMonths($i)->locale('es')->isoFormat('MMM YYYY'));
        }

        return [
            'labels' => $meses->toArray(),
            'data' => $expedientesData,
        ];
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

    {{-- Breadcrumbs de navegación --}}
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
                <a href="{{ route('admin.usuarios.index') }}" wire:navigate class="gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Usuarios
                </a>
            </li>
            <li>
                <span class="inline-flex items-center gap-1 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    {{ $usuario->nombre_completo }}
                </span>
            </li>
        </ul>
    </div>

    {{-- Tarjeta de Perfil Principal --}}
    <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-xl border border-base-300 overflow-hidden">
        {{-- Header decorativo --}}
        <div class="h-24 bg-gradient-to-r from-primary/20 via-secondary/20 to-accent/20"></div>

        <div class="card-body -mt-16">
            {{-- Encabezado con avatar y nombre --}}
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Avatar con indicador de estado --}}
                <div class="indicator">
                    <span
                        class="indicator-item indicator-bottom indicator-end badge {{ $usuario->estaActivo() ? 'badge-success' : 'badge-error' }} badge-sm"></span>
                    <div class="avatar placeholder">
                        <div
                            class="bg-primary text-primary-content rounded-full w-28 h-28 ring-4 ring-base-100 shadow-lg flex justify-center items-center">
                            <span class="text-4xl font-bold">{{ $usuario->iniciales }}</span>
                        </div>
                    </div>
                </div>

                {{-- Información principal --}}
                <div class="flex-1 text-center sm:text-left">
                    <h1 class="text-3xl font-bold tracking-tight">{{ $usuario->nombre_completo }}</h1>
                    @if ($usuario->cargo)
                        <p class="text-base-content/70 text-lg">{{ $usuario->cargo }}</p>
                    @endif
                    <div class="flex items-center justify-center sm:justify-start gap-2 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 text-base-content/50">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <span class="text-sm text-base-content/60">{{ $usuario->email }}</span>
                    </div>

                    {{-- Badges de rol y estado --}}
                    <div class="flex flex-wrap gap-2 mt-4 justify-center sm:justify-start">
                        <div
                            class="badge badge-lg gap-2
                            @if ($usuario->isAdmin()) badge-secondary
                            @elseif($usuario->isDirector()) badge-primary
                            @elseif($usuario->isJefeFinanciero()) badge-warning
                            @elseif($usuario->isTecnico()) badge-info
                            @else badge-ghost @endif">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            {{ $usuario->role->nombre }}
                        </div>
                        <div
                            class="badge badge-lg gap-1 {{ $usuario->estaActivo() ? 'badge-success' : 'badge-error' }}">
                            @if ($usuario->estaActivo())
                                <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                            @endif
                            {{ $usuario->estaActivo() ? 'Activo' : 'Inactivo' }}
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row gap-2">
                    @if (!$usuario->isAdmin())
                        <button wire:click="cambiarEstado"
                            class="btn {{ $usuario->estaActivo() ? 'btn-outline btn-error' : 'btn-success' }} btn-sm gap-2">
                            @if ($usuario->estaActivo())
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
                    @endif
                    <button wire:click="editar" class="btn btn-primary btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                        Editar perfil
                    </button>
                </div>
            </div>

            <div class="divider my-4"></div>

            {{-- Detalles del usuario en grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Teléfono --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 text-primary rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Teléfono</p>
                            <p class="font-semibold">{{ $usuario->telefono ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Fecha de registro --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-info/10 text-info rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Fecha de registro</p>
                            <p class="font-semibold">{{ $usuario->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Último acceso --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="bg-success/10 text-success rounded-btn p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Última actualización</p>
                            <p class="font-semibold">{{ $usuario->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                {{-- 2FA --}}
                <div class="bg-base-200/50 rounded-box p-4 hover:bg-base-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-btn p-3 {{ $usuario->two_factor_secret ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase tracking-wider">Autenticación 2FA</p>
                            <p
                                class="font-semibold {{ $usuario->two_factor_secret ? 'text-success' : 'text-warning' }}">
                                {{ $usuario->two_factor_secret ? 'Habilitada' : 'Deshabilitada' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Municipios asignados --}}
    @if ($usuario->municipios->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-lg gap-3">
                    <div class="bg-accent/10 text-accent rounded-btn p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </div>
                    Municipios Asignados
                    <span class="badge badge-neutral badge-sm">{{ $usuario->municipios->count() }}</span>
                </h2>
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach ($usuario->municipios as $municipio)
                        <span
                            class="badge badge-outline badge-lg gap-2 hover:badge-primary transition-colors cursor-default">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            {{ $municipio->nombre }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Estadísticas (solo para técnicos o usuarios con expedientes) --}}
    @if ($usuario->isTecnico() || $this->estadisticas['total_expedientes'] > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total Expedientes --}}
            <div
                class="stat bg-gradient-to-br from-primary/5 to-primary/10 rounded-box shadow border border-primary/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-primary opacity-80">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                    </svg>
                </div>
                <div class="stat-title text-primary/70">Total Expedientes</div>
                <div class="stat-value text-primary">{{ $this->estadisticas['total_expedientes'] }}</div>
                <div class="stat-desc">Registrados</div>
            </div>

            {{-- Aprobados --}}
            <div
                class="stat bg-gradient-to-br from-success/5 to-success/10 rounded-box shadow border border-success/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-success opacity-80">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title text-success/70">Aprobados</div>
                <div class="stat-value text-success">{{ $this->estadisticas['expedientes_aprobados'] }}</div>
                <div class="stat-desc">Finalizados</div>
            </div>

            {{-- Pendientes --}}
            <div
                class="stat bg-gradient-to-br from-warning/5 to-warning/10 rounded-box shadow border border-warning/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-warning opacity-80">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title text-warning/70">Pendientes</div>
                <div class="stat-value text-warning">{{ $this->estadisticas['expedientes_pendientes'] }}</div>
                <div class="stat-desc">En proceso</div>
            </div>

            {{-- Rechazados --}}
            <div
                class="stat bg-gradient-to-br from-error/5 to-error/10 rounded-box shadow border border-error/20 hover:shadow-lg transition-shadow">
                <div class="stat-figure text-error opacity-80">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title text-error/70">Rechazados</div>
                <div class="stat-value text-error">{{ $this->estadisticas['expedientes_rechazados'] }}</div>
                <div class="stat-desc">No aprobados</div>
            </div>
        </div>

        {{-- Gráfica de expedientes por mes --}}
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <h2 class="card-title text-lg gap-3">
                    <div class="bg-secondary/10 text-secondary rounded-btn p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    Expedientes por Mes
                    <span class="badge badge-ghost badge-sm font-normal">Últimos 6 meses</span>
                </h2>
                <div class="w-full h-72 mt-2" x-data="chartComponent(@js($this->chartData))" x-init="initChart()">
                    <canvas x-ref="chart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        {{-- Últimos expedientes --}}
        @if ($this->ultimosExpedientes->isNotEmpty())
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 class="card-title text-lg gap-3">
                            <div class="bg-info/10 text-info rounded-btn p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            Últimos Expedientes
                        </h2>
                        <a href="{{ route('expedientes.index') }}" wire:navigate
                            class="btn btn-primary btn-sm btn-outline gap-1">
                            Ver todos
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>Código SNIP</th>
                                    <th>Proyecto</th>
                                    <th>Municipio</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->ultimosExpedientes as $expediente)
                                    <tr class="hover:bg-base-200/50 transition-colors">
                                        <td>
                                            <span
                                                class="font-mono font-bold text-primary">{{ $expediente->codigo_snip }}</span>
                                        </td>
                                        <td>
                                            <div class="tooltip tooltip-right"
                                                data-tip="{{ $expediente->nombre_proyecto }}">
                                                <div class="max-w-xs truncate font-medium">
                                                    {{ $expediente->nombre_proyecto }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-ghost badge-sm">{{ $expediente->municipio->nombre }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="text-sm text-base-content/70">{{ $expediente->tipoSolicitud->nombre ?? '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge badge-sm gap-1
                                                @if ($expediente->estado === 'Aprobado') badge-success
                                                @elseif($expediente->estado === 'Rechazado') badge-error
                                                @elseif($expediente->estado === 'En Revisión') badge-warning
                                                @elseif($expediente->estado === 'Recibido') badge-info
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
                                                class="text-sm text-base-content/60">{{ $expediente->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="tooltip" data-tip="Ver expediente">
                                                <a href="{{ route('expedientes.show', $expediente->id) }}"
                                                    wire:navigate class="btn btn-ghost btn-xs btn-circle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Modal Editar Usuario --}}
    <livewire:modals.usuario-modal />
</div>

{{-- Script para Chart.js con colores dinámicos del tema --}}
@script
    <script>
        Alpine.data('chartComponent', (chartData) => ({
            chart: null,
            chartData: chartData,

            initChart() {
                const ctx = this.$refs.chart.getContext('2d');

                // Obtener colores del tema DaisyUI
                const computedStyle = getComputedStyle(document.documentElement);
                const primaryColor = computedStyle.getPropertyValue('--p') ?
                    `oklch(${computedStyle.getPropertyValue('--p')})` : 'rgb(59, 130, 246)';
                const baseContent = computedStyle.getPropertyValue('--bc') ?
                    `oklch(${computedStyle.getPropertyValue('--bc')})` : 'rgb(100, 116, 139)';

                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.chartData.labels,
                        datasets: [{
                            label: 'Expedientes',
                            data: this.chartData.data,
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
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.parsed.y} expediente${context.parsed.y !== 1 ? 's' : ''}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }
        }));
    </script>
@endscript
