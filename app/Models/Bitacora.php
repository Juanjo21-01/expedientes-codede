<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    // Nombre de la tabla
    protected $table = 'bitacoras';

    // ---- Constantes de Entidad ----
    public const ENTIDAD_GUIA = 'Guía';
    public const ENTIDAD_EXPEDIENTE = 'Expediente';
    public const ENTIDAD_PROYECTO = 'Proyecto';
    public const ENTIDAD_USUARIO = 'Usuario';

    // ---- Constantes de Tipo ----
    public const TIPO_CREACION = 'Creación';
    public const TIPO_ELIMINACION = 'Eliminación';
    public const TIPO_REPORTE = 'Reporte';
    public const TIPO_CAMBIO_ESTADO = 'Cambio de Estado';
    public const TIPO_REVISION = 'Revisión';

    // Atributos asignables
    protected $fillable = [
        'user_id',
        'entidad',
        'tipo',
        'detalle',
    ];

    // ---- Relaciones ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ---- Scopes ----

    /**
     * Filtrar por entidad
     */
    public function scopeDeEntidad(Builder $query, string $entidad): Builder
    {
        return $query->where('entidad', $entidad);
    }

    /**
     * Filtrar por tipo
     */
    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Filtrar por usuario
     */
    public function scopeDeUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Registros de expedientes
     */
    public function scopeDeExpedientes(Builder $query): Builder
    {
        return $query->deEntidad(self::ENTIDAD_EXPEDIENTE);
    }

    /**
     * Registros de usuarios
     */
    public function scopeDeUsuarios(Builder $query): Builder
    {
        return $query->deEntidad(self::ENTIDAD_USUARIO);
    }

    /**
     * Registros de guías
     */
    public function scopeDeGuias(Builder $query): Builder
    {
        return $query->deEntidad(self::ENTIDAD_GUIA);
    }

    /**
     * Registros de hoy
     */
    public function scopeDeHoy(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Registros de este mes
     */
    public function scopeDeEsteMes(Builder $query): Builder
    {
        return $query->whereYear('created_at', now()->year)
                     ->whereMonth('created_at', now()->month);
    }

    /**
     * Ordenar por más recientes
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Buscar en detalle
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where('detalle', 'like', "%{$termino}%");
    }

    // ---- Métodos de registro (Factory methods) ----

    /**
     * Registrar una acción en la bitácora
     */
    public static function registrar(string $entidad, string $tipo, string $detalle, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'entidad' => $entidad,
            'tipo' => $tipo,
            'detalle' => $detalle,
        ]);
    }

    /**
     * Registrar creación de expediente
     */
    public static function registrarCreacionExpediente(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_CREACION, $detalle, $userId);
    }

    /**
     * Registrar cambio de estado de expediente
     */
    public static function registrarCambioEstadoExpediente(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_CAMBIO_ESTADO, $detalle, $userId);
    }

    /**
     * Registrar revisión
     */
    public static function registrarRevision(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_REVISION, $detalle, $userId);
    }

    /**
     * Registrar creación de usuario
     */
    public static function registrarCreacionUsuario(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_USUARIO, self::TIPO_CREACION, $detalle, $userId);
    }

    /**
     * Registrar eliminación de usuario
     */
    public static function registrarEliminacionUsuario(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_USUARIO, self::TIPO_ELIMINACION, $detalle, $userId);
    }

    /**
     * Registrar creación de guía
     */
    public static function registrarCreacionGuia(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_GUIA, self::TIPO_CREACION, $detalle, $userId);
    }

    /**
     * Registrar generación de reporte
     */
    public static function registrarReporte(string $detalle, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_REPORTE, $detalle, $userId);
    }

    // ---- Accesores ----

    /**
     * Badge de la entidad
     */
    public function getEntidadBadgeClassAttribute(): string
    {
        return match ($this->entidad) {
            self::ENTIDAD_EXPEDIENTE => 'badge-primary',
            self::ENTIDAD_USUARIO => 'badge-secondary',
            self::ENTIDAD_GUIA => 'badge-accent',
            self::ENTIDAD_PROYECTO => 'badge-info',
            default => 'badge-ghost',
        };
    }

    /**
     * Badge del tipo
     */
    public function getTipoBadgeClassAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_CREACION => 'badge-success',
            self::TIPO_ELIMINACION => 'badge-error',
            self::TIPO_CAMBIO_ESTADO => 'badge-warning',
            self::TIPO_REVISION => 'badge-info',
            self::TIPO_REPORTE => 'badge-accent',
            default => 'badge-ghost',
        };
    }

    // ---- Métodos estáticos ----

    public static function getEntidades(): array
    {
        return [
            self::ENTIDAD_GUIA,
            self::ENTIDAD_EXPEDIENTE,
            self::ENTIDAD_PROYECTO,
            self::ENTIDAD_USUARIO,
        ];
    }

    public static function getTipos(): array
    {
        return [
            self::TIPO_CREACION,
            self::TIPO_ELIMINACION,
            self::TIPO_REPORTE,
            self::TIPO_CAMBIO_ESTADO,
            self::TIPO_REVISION,
        ];
    }
}
