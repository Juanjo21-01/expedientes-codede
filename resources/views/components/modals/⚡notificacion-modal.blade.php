<?php

use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\NotificacionEnviada;
use App\Models\TipoNotificacion;
use App\Models\Expediente;
use App\Models\Municipio;
use App\Mail\NotificacionMail;
use Illuminate\Support\Facades\Mail;

new class extends Component {
    // Estado del modal
    public bool $mostrar = false;
    public bool $enviando = false;

    // Contexto (de dónde se abrió)
    public ?int $expedienteId = null;
    public ?int $municipioId = null;

    // Campos del formulario
    #[Validate('required', message: 'Seleccione un tipo de notificación')]
    public string $tipo_notificacion_id = '';

    #[Validate('required|email', message: 'Ingrese un email válido')]
    public string $destinatario_email = '';

    public string $destinatario_nombre = '';

    #[Validate('required|max:255', message: 'El asunto es requerido')]
    public string $asunto = '';

    #[Validate('required|min:10', message: 'El mensaje debe tener al menos 10 caracteres')]
    public string $mensaje = '';

    // Abrir modal desde expediente
    #[On('abrir-notificacion-modal')]
    public function abrir(?int $expedienteId = null, ?int $municipioId = null): void
    {
        $this->resetForm();
        $this->expedienteId = $expedienteId;
        $this->municipioId = $municipioId;

        // Auto-completar datos según contexto
        if ($expedienteId) {
            $expediente = Expediente::with('municipio')->find($expedienteId);
            if ($expediente) {
                $this->municipioId = $expediente->municipio_id;
                $this->asunto = "Expediente {$expediente->codigo_snip} - ";

                // Auto-completar email del contacto del municipio
                if ($expediente->municipio && $expediente->municipio->contacto_email) {
                    $this->destinatario_email = $expediente->municipio->contacto_email;
                    $this->destinatario_nombre = $expediente->municipio->contacto_nombre ?? '';
                }
            }
        } elseif ($municipioId) {
            $municipio = Municipio::find($municipioId);
            if ($municipio) {
                $this->asunto = "Municipio {$municipio->nombre} - ";
                if ($municipio->contacto_email) {
                    $this->destinatario_email = $municipio->contacto_email;
                    $this->destinatario_nombre = $municipio->contacto_nombre ?? '';
                }
            }
        }

        $this->mostrar = true;
    }

    // Tipos de notificación disponibles
    #[Computed]
    public function tiposNotificacion()
    {
        return TipoNotificacion::ordenados()->get();
    }

    // Expediente relacionado (para mostrar info)
    #[Computed]
    public function expediente()
    {
        return $this->expedienteId ? Expediente::with('municipio')->find($this->expedienteId) : null;
    }

    // Municipio relacionado (para mostrar info)
    #[Computed]
    public function municipio()
    {
        return $this->municipioId ? Municipio::find($this->municipioId) : null;
    }

    // Auto-generar asunto al cambiar tipo de notificación
    public function updatedTipoNotificacionId($value): void
    {
        if ($value) {
            $tipo = TipoNotificacion::find($value);
            if ($tipo) {
                $prefijo = '';
                if ($this->expedienteId) {
                    $exp = Expediente::find($this->expedienteId);
                    $prefijo = $exp ? "Expediente {$exp->codigo_snip}" : '';
                } elseif ($this->municipioId) {
                    $mun = Municipio::find($this->municipioId);
                    $prefijo = $mun ? "Municipio {$mun->nombre}" : '';
                }

                $this->asunto = $prefijo ? "{$tipo->nombre} - {$prefijo}" : $tipo->nombre;
            }
        }
    }

    // Enviar notificación
    public function enviar(): void
    {
        $this->validate();
        $this->enviando = true;

        try {
            // Crear registro de notificación
            $notificacion = NotificacionEnviada::create([
                'tipo_notificacion_id' => $this->tipo_notificacion_id,
                'expediente_id' => $this->expedienteId,
                'municipio_id' => $this->municipioId,
                'remitente_id' => auth()->id(),
                'destinatario_email' => $this->destinatario_email,
                'destinatario_nombre' => $this->destinatario_nombre ?: null,
                'asunto' => $this->asunto,
                'mensaje' => $this->mensaje,
                'estado' => NotificacionEnviada::ESTADO_PENDIENTE,
            ]);

            // Cargar relaciones para el Mailable
            $notificacion->load(['remitente', 'expediente.municipio', 'municipio', 'tipoNotificacion']);

            // Enviar correo
            Mail::to($this->destinatario_email)->send(new NotificacionMail($notificacion));

            // Marcar como enviada
            $notificacion->marcarEnviada();

            $this->cerrar();
            $this->dispatch('notificacion-enviada');
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Notificación enviada correctamente a ' . $this->destinatario_email);
        } catch (\Exception $e) {
            // Si existe la notificación, marcarla como fallida
            if (isset($notificacion) && $notificacion->exists) {
                $notificacion->marcarFallida();
            }

            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al enviar la notificación: ' . $e->getMessage());
        } finally {
            $this->enviando = false;
        }
    }

    // Cerrar modal
    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    // Reset formulario
    private function resetForm(): void
    {
        $this->reset(['tipo_notificacion_id', 'destinatario_email', 'destinatario_nombre', 'asunto', 'mensaje', 'expedienteId', 'municipioId', 'enviando']);
        $this->resetValidation();
    }
};
?>
<div>
    @if ($mostrar)
        <div class="modal modal-open" wire:keydown.escape.window="cerrar">
            <div class="modal-box max-w-2xl" wire:click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <x-heroicon-o-envelope class="w-5 h-5 text-primary" />
                        Enviar Notificación
                    </h3>
                    <button wire:click="cerrar" class="btn btn-ghost btn-sm btn-circle">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Contexto (info del expediente o municipio) --}}
                @if ($this->expediente)
                    <div class="alert alert-info mb-4">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                        <div>
                            <p class="font-semibold text-sm">Expediente: {{ $this->expediente->codigo_snip }}</p>
                            <p class="text-xs">{{ $this->expediente->nombre_proyecto }} ·
                                {{ $this->expediente->municipio->nombre ?? '' }}</p>
                        </div>
                    </div>
                @elseif ($this->municipio)
                    <div class="alert alert-info mb-4">
                        <x-heroicon-o-building-office-2 class="w-5 h-5" />
                        <div>
                            <p class="font-semibold text-sm">Municipio: {{ $this->municipio->nombre }}</p>
                            <p class="text-xs">{{ $this->municipio->departamento }}</p>
                        </div>
                    </div>
                @endif

                <form wire:submit="enviar">
                    <div class="space-y-4">
                        {{-- Tipo de notificación --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Tipo de notificación <span class="text-error">*</span>
                            </legend>
                            <select wire:model.live="tipo_notificacion_id" class="select w-full">
                                <option value="" selected disabled>Seleccione un tipo...</option>
                                @foreach ($this->tiposNotificacion as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('tipo_notificacion_id')
                                <p class="label text-error text-xs">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        {{-- Destinatario --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Email destinatario <span class="text-error">*</span>
                                </legend>
                                <input type="email" wire:model="destinatario_email" class="input w-full"
                                    placeholder="correo@ejemplo.com" />
                                @error('destinatario_email')
                                    <p class="label text-error text-xs">{{ $message }}</p>
                                @enderror
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Nombre destinatario</legend>
                                <input type="text" wire:model="destinatario_nombre" class="input w-full"
                                    placeholder="Nombre del destinatario" />
                            </fieldset>
                        </div>

                        {{-- Asunto --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Asunto <span class="text-error">*</span></legend>
                            <input type="text" wire:model="asunto" class="input w-full"
                                placeholder="Asunto del correo" />
                            @error('asunto')
                                <p class="label text-error text-xs">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        {{-- Mensaje --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Mensaje <span class="text-error">*</span></legend>
                            <textarea wire:model="mensaje" class="textarea w-full h-32" placeholder="Escriba el mensaje de la notificación..."></textarea>
                            @error('mensaje')
                                <p class="label text-error text-xs">{{ $message }}</p>
                            @enderror
                            <p class="label text-xs text-base-content/50">Mínimo 10 caracteres</p>
                        </fieldset>
                    </div>

                    {{-- Acciones --}}
                    <div class="modal-action">
                        <button type="button" wire:click="cerrar" class="btn btn-ghost" wire:loading.attr="disabled">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="enviar">
                                <x-heroicon-o-paper-airplane class="w-4 h-4" />
                            </span>
                            <span wire:loading wire:target="enviar" class="loading loading-spinner loading-sm"></span>
                            <span wire:loading.remove wire:target="enviar">Enviar Notificación</span>
                            <span wire:loading wire:target="enviar">Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="cerrar"></div>
        </div>
    @endif
</div>
