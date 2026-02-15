<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionEnviada extends Model
{
    // Nombre de la tabla
    protected $table = 'notificaciones_enviadas';

    // ---- Constantes de Estado ----
    public const ESTADO_PENDIENTE = 'Pendiente';
    public const ESTADO_ENVIADO = 'Enviado';
    public const ESTADO_FALLIDO = 'Fallido';

    // Atributos asignables
    protected $fillable = [
        'tipo_notificacion_id',
        'expediente_id',
        'municipio_id',
        'remitente_id',
        'destinatario_email',
        'destinatario_nombre',
        'asunto',
        'mensaje',
        'enviado_at',
        'estado',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'enviado_at' => 'datetime',
        ];
    }

    // ---- Relaciones ----

    public function tipoNotificacion(): BelongsTo
    {
        return $this->belongsTo(TipoNotificacion::class, 'tipo_notificacion_id');
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    // ---- Scopes ----

    /**
     * Notificaciones pendientes
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    /**
     * Notificaciones enviadas
     */
    public function scopeEnviadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ENVIADO);
    }

    /**
     * Notificaciones fallidas
     */
    public function scopeFallidas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_FALLIDO);
    }

    /**
     * Filtrar por expediente
     */
    public function scopeDeExpediente(Builder $query, int $expedienteId): Builder
    {
        return $query->where('expediente_id', $expedienteId);
    }

    /**
     * Filtrar por municipio
     */
    public function scopeDeMunicipio(Builder $query, int $municipioId): Builder
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Filtrar por remitente
     */
    public function scopeDeRemitente(Builder $query, int $userId): Builder
    {
        return $query->where('remitente_id', $userId);
    }

    /**
     * Filtrar por destinatario
     */
    public function scopeParaDestinatario(Builder $query, string $email): Builder
    {
        return $query->where('destinatario_email', $email);
    }

    /**
     * Filtrar por tipo de notificación
     */
    public function scopeDeTipo(Builder $query, int $tipoId): Builder
    {
        return $query->where('tipo_notificacion_id', $tipoId);
    }

    /**
     * Enviadas hoy
     */
    public function scopeEnviadasHoy(Builder $query): Builder
    {
        return $query->whereDate('enviado_at', today());
    }

    /**
     * Ordenar por más recientes
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Buscar por asunto, destinatario o mensaje
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('asunto', 'like', "%{$termino}%")
              ->orWhere('destinatario_email', 'like', "%{$termino}%")
              ->orWhere('destinatario_nombre', 'like', "%{$termino}%")
              ->orWhere('mensaje', 'like', "%{$termino}%");
        });
    }

    /**
     * Accesibles por un usuario (según rol)
     * Admin y Director ven todas, los demás solo las suyas o relacionadas
     */
    public function scopeAccesiblesPor(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Las que envió
            $q->where('remitente_id', $user->id);

            // Las relacionadas con sus expedientes
            if ($user->isTecnico() || $user->isJefeFinanciero()) {
                $q->orWhereHas('expediente', function ($eq) use ($user) {
                    $eq->accesiblesPor($user);
                });
            }

            // Las relacionadas con sus municipios
            if ($user->isMunicipal()) {
                $municipioIds = $user->municipios->pluck('id')->toArray();
                $q->orWhereIn('municipio_id', $municipioIds);
            }
        });
    }

    // ---- Helpers de Estado ----

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function fueEnviada(): bool
    {
        return $this->estado === self::ESTADO_ENVIADO;
    }

    public function fallo(): bool
    {
        return $this->estado === self::ESTADO_FALLIDO;
    }

    // ---- Métodos de Acción ----

    /**
     * Marcar como enviada
     */
    public function marcarEnviada(): bool
    {
        $this->estado = self::ESTADO_ENVIADO;
        $this->enviado_at = now();
        return $this->save();
    }

    /**
     * Marcar como fallida
     */
    public function marcarFallida(): bool
    {
        $this->estado = self::ESTADO_FALLIDO;
        return $this->save();
    }

    /**
     * Reintentar (volver a pendiente)
     */
    public function reintentar(): bool
    {
        $this->estado = self::ESTADO_PENDIENTE;
        $this->enviado_at = null;
        return $this->save();
    }

    // ---- Accesores ----

    /**
     * Badge del estado
     */
    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'badge-warning',
            self::ESTADO_ENVIADO => 'badge-success',
            self::ESTADO_FALLIDO => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Ícono del estado
     */
    public function getEstadoIconoAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'clock',
            self::ESTADO_ENVIADO => 'check-circle',
            self::ESTADO_FALLIDO => 'x-circle',
            default => 'question-mark-circle',
        };
    }

    /**
     * Contexto de la notificación (expediente o municipio)
     */
    public function getContextoAttribute(): string
    {
        if ($this->expediente_id && $this->expediente) {
            return "Expediente {$this->expediente->codigo_snip}";
        }

        if ($this->municipio_id && $this->municipio) {
            return "Municipio {$this->municipio->nombre}";
        }

        return 'General';
    }

    // ---- Métodos estáticos ----

    public static function getEstados(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_ENVIADO,
            self::ESTADO_FALLIDO,
        ];
    }
}
