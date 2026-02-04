<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RevisionFinanciera extends Model
{
    // Nombre de la tabla
    protected $table = 'revisiones_financieras';

    // ---- Constantes de Estado ----
    public const ESTADO_COMPLETO = 'Completo';
    public const ESTADO_INCOMPLETO = 'Incompleto';

    // ---- Constantes de Acción ----
    public const ACCION_APROBAR = 'Aprobar';
    public const ACCION_RECHAZAR = 'Rechazar';
    public const ACCION_SOLICITAR_CORRECCIONES = 'SolicitarCorrecciones';

    // Atributos asignables
    protected $fillable = [
        'expediente_id',
        'revisor_id',
        'estado',
        'accion',
        'monto_aprobado',
        'observaciones',
        'fecha_revision',
        'fecha_complemento',
        'dias_transcurridos',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'fecha_revision' => 'datetime',
            'fecha_complemento' => 'datetime',
            'monto_aprobado' => 'decimal:2',
            'dias_transcurridos' => 'integer',
        ];
    }

    // ---- Relaciones ----

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    // ---- Scopes ----

    /**
     * Revisiones completas
     */
    public function scopeCompletas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_COMPLETO);
    }

    /**
     * Revisiones incompletas
     */
    public function scopeIncompletas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_INCOMPLETO);
    }

    /**
     * Filtrar por revisor
     */
    public function scopeDeRevisor(Builder $query, int $revisorId): Builder
    {
        return $query->where('revisor_id', $revisorId);
    }

    /**
     * Filtrar por expediente
     */
    public function scopeDeExpediente(Builder $query, int $expedienteId): Builder
    {
        return $query->where('expediente_id', $expedienteId);
    }

    /**
     * Revisiones de este mes
     */
    public function scopeDeEsteMes(Builder $query): Builder
    {
        return $query->whereYear('fecha_revision', now()->year)
                     ->whereMonth('fecha_revision', now()->month);
    }

    /**
     * Revisiones pendientes de complemento
     */
    public function scopePendientesComplemento(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_INCOMPLETO)
                     ->whereNull('fecha_complemento');
    }

    /**
     * Ordenar por más recientes
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderBy('fecha_revision', 'desc');
    }

    // ---- Helpers de Estado ----

    public function estaCompleta(): bool
    {
        return $this->estado === self::ESTADO_COMPLETO;
    }

    public function estaIncompleta(): bool
    {
        return $this->estado === self::ESTADO_INCOMPLETO;
    }

    // ---- Helpers de Acción ----

    public function fueAprobada(): bool
    {
        return $this->accion === self::ACCION_APROBAR;
    }

    public function fueRechazada(): bool
    {
        return $this->accion === self::ACCION_RECHAZAR;
    }

    public function solicitoCorrecciones(): bool
    {
        return $this->accion === self::ACCION_SOLICITAR_CORRECCIONES;
    }

    public function tieneAccion(): bool
    {
        return !empty($this->accion);
    }

    // ---- Helpers de Complemento ----

    public function tieneComplemento(): bool
    {
        return !empty($this->fecha_complemento);
    }

    public function registrarComplemento(): bool
    {
        $this->fecha_complemento = now();
        $this->dias_transcurridos = $this->fecha_revision->diffInDays(now());
        return $this->save();
    }

    // ---- Accesores ----

    /**
     * Monto formateado
     */
    public function getMontoFormateadoAttribute(): string
    {
        return $this->monto_aprobado 
            ? 'Q ' . number_format($this->monto_aprobado, 2) 
            : 'N/A';
    }

    /**
     * Badge del estado
     */
    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_COMPLETO => 'badge-success',
            self::ESTADO_INCOMPLETO => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Badge de la acción
     */
    public function getAccionBadgeClassAttribute(): string
    {
        return match ($this->accion) {
            self::ACCION_APROBAR => 'badge-success',
            self::ACCION_RECHAZAR => 'badge-error',
            self::ACCION_SOLICITAR_CORRECCIONES => 'badge-warning',
            default => 'badge-ghost',
        };
    }

    /**
     * Texto legible de la acción
     */
    public function getAccionTextoAttribute(): string
    {
        return match ($this->accion) {
            self::ACCION_APROBAR => 'Aprobado',
            self::ACCION_RECHAZAR => 'Rechazado',
            self::ACCION_SOLICITAR_CORRECCIONES => 'Solicitar Correcciones',
            default => 'Sin acción',
        };
    }

    // ---- Métodos estáticos ----

    public static function getEstados(): array
    {
        return [
            self::ESTADO_COMPLETO,
            self::ESTADO_INCOMPLETO,
        ];
    }

    public static function getAcciones(): array
    {
        return [
            self::ACCION_APROBAR,
            self::ACCION_RECHAZAR,
            self::ACCION_SOLICITAR_CORRECCIONES,
        ];
    }
}
