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
        'destinatario_email',
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
     * Filtrar por destinatario
     */
    public function scopeParaDestinatario(Builder $query, string $email): Builder
    {
        return $query->where('destinatario_email', $email);
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
