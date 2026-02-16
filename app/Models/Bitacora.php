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
    public const ENTIDAD_AUDITORIA = 'Auditoría';
    public const ENTIDAD_USUARIO = 'Usuario';
    public const ENTIDAD_NOTIFICACION = 'Notificación';

    // ---- Constantes de Tipo ----
    public const TIPO_CREACION = 'Creación';
    public const TIPO_ELIMINACION = 'Eliminación';
    public const TIPO_EDICION = 'Edición';
    public const TIPO_REPORTE = 'Reporte';
    public const TIPO_CAMBIO_ESTADO = 'Cambio de Estado';
    public const TIPO_REVISION = 'Revisión';
    public const TIPO_NOTIFICACION = 'Notificación';

    // Atributos asignables
    protected $fillable = [
        'user_id',
        'entidad',
        'entidad_id',
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
     * Filtrar por entidad e id específico
     */
    public function scopeDeEntidadEspecifica(Builder $query, string $entidad, int $entidadId): Builder
    {
        return $query->where('entidad', $entidad)->where('entidad_id', $entidadId);
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
     * Registros de notificaciones
     */
    public function scopeDeNotificaciones(Builder $query): Builder
    {
        return $query->deEntidad(self::ENTIDAD_NOTIFICACION);
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
     * Filtrar por rango de fechas
     */
    public function scopeEntreFechas(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
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
    public static function registrar(string $entidad, string $tipo, string $detalle, ?int $entidadId = null, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'tipo' => $tipo,
            'detalle' => $detalle,
        ]);
    }

    // ---- Expedientes ----

    /**
     * Registrar creación de expediente
     */
    public static function registrarCreacionExpediente(string $detalle, ?int $expedienteId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_CREACION, $detalle, $expedienteId, $userId);
    }

    /**
     * Registrar edición de expediente
     */
    public static function registrarEdicionExpediente(string $detalle, ?int $expedienteId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_EDICION, $detalle, $expedienteId, $userId);
    }

    /**
     * Registrar eliminación de expediente
     */
    public static function registrarEliminacionExpediente(string $detalle, ?int $expedienteId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_ELIMINACION, $detalle, $expedienteId, $userId);
    }

    /**
     * Registrar cambio de estado de expediente
     */
    public static function registrarCambioEstadoExpediente(string $detalle, ?int $expedienteId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_CAMBIO_ESTADO, $detalle, $expedienteId, $userId);
    }

    /**
     * Registrar revisión financiera
     */
    public static function registrarRevision(string $detalle, ?int $expedienteId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_EXPEDIENTE, self::TIPO_REVISION, $detalle, $expedienteId, $userId);
    }

    /**
     * Registrar generación de reporte
     */
    public static function registrarReporte(string $detalle, ?string $entidad = null, ?int $userId = null): self
    {
        return self::registrar($entidad ?? self::ENTIDAD_EXPEDIENTE, self::TIPO_REPORTE, $detalle, null, $userId);
    }

    // ---- Usuarios ----

    /**
     * Registrar creación de usuario
     */
    public static function registrarCreacionUsuario(string $detalle, ?int $usuarioId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_USUARIO, self::TIPO_CREACION, $detalle, $usuarioId, $userId);
    }

    /**
     * Registrar edición de usuario
     */
    public static function registrarEdicionUsuario(string $detalle, ?int $usuarioId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_USUARIO, self::TIPO_EDICION, $detalle, $usuarioId, $userId);
    }

    /**
     * Registrar eliminación de usuario
     */
    public static function registrarEliminacionUsuario(string $detalle, ?int $usuarioId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_USUARIO, self::TIPO_ELIMINACION, $detalle, $usuarioId, $userId);
    }

    /**
     * Registrar cambio de estado de usuario (activar/desactivar)
     */
    public static function registrarCambioEstadoUsuario(string $detalle, ?int $usuarioId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_USUARIO, self::TIPO_CAMBIO_ESTADO, $detalle, $usuarioId, $userId);
    }

    // ---- Guías ----

    /**
     * Registrar creación de guía
     */
    public static function registrarCreacionGuia(string $detalle, ?int $guiaId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_GUIA, self::TIPO_CREACION, $detalle, $guiaId, $userId);
    }

    /**
     * Registrar edición de guía
     */
    public static function registrarEdicionGuia(string $detalle, ?int $guiaId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_GUIA, self::TIPO_EDICION, $detalle, $guiaId, $userId);
    }

    /**
     * Registrar eliminación de guía
     */
    public static function registrarEliminacionGuia(string $detalle, ?int $guiaId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_GUIA, self::TIPO_ELIMINACION, $detalle, $guiaId, $userId);
    }

    /**
     * Registrar cambio de estado de guía (activar/desactivar)
     */
    public static function registrarCambioEstadoGuia(string $detalle, ?int $guiaId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_GUIA, self::TIPO_CAMBIO_ESTADO, $detalle, $guiaId, $userId);
    }

    // ---- Notificaciones ----

    /**
     * Registrar envío de notificación
     */
    public static function registrarNotificacion(string $detalle, ?int $notificacionId = null, ?int $userId = null): self
    {
        return self::registrar(self::ENTIDAD_NOTIFICACION, self::TIPO_NOTIFICACION, $detalle, $notificacionId, $userId);
    }

    // ---- Accesores ----

    /**
     * Badge CSS class de la entidad
     */
    public function getEntidadBadgeClassAttribute(): string
    {
        return match ($this->entidad) {
            self::ENTIDAD_EXPEDIENTE => 'badge-primary',
            self::ENTIDAD_USUARIO => 'badge-secondary',
            self::ENTIDAD_GUIA => 'badge-accent',
            self::ENTIDAD_AUDITORIA => 'badge-info',
            self::ENTIDAD_NOTIFICACION => 'badge-warning',
            default => 'badge-ghost',
        };
    }

    /**
     * Badge CSS class del tipo
     */
    public function getTipoBadgeClassAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_CREACION => 'badge-success',
            self::TIPO_ELIMINACION => 'badge-error',
            self::TIPO_EDICION => 'badge-info',
            self::TIPO_CAMBIO_ESTADO => 'badge-warning',
            self::TIPO_REVISION => 'badge-info',
            self::TIPO_REPORTE => 'badge-accent',
            self::TIPO_NOTIFICACION => 'badge-warning',
            default => 'badge-ghost',
        };
    }

    /**
     * Ícono representativo del tipo
     */
    public function getIconoTipoAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_CREACION => 'heroicon-o-plus-circle',
            self::TIPO_ELIMINACION => 'heroicon-o-trash',
            self::TIPO_EDICION => 'heroicon-o-pencil-square',
            self::TIPO_CAMBIO_ESTADO => 'heroicon-o-arrow-path',
            self::TIPO_REVISION => 'heroicon-o-clipboard-document-check',
            self::TIPO_REPORTE => 'heroicon-o-document-arrow-down',
            self::TIPO_NOTIFICACION => 'heroicon-o-envelope',
            default => 'heroicon-o-information-circle',
        };
    }

    // ---- Métodos estáticos ----

    public static function getEntidades(): array
    {
        return [
            self::ENTIDAD_GUIA,
            self::ENTIDAD_EXPEDIENTE,
            self::ENTIDAD_AUDITORIA,
            self::ENTIDAD_USUARIO,
            self::ENTIDAD_NOTIFICACION,
        ];
    }

    public static function getTipos(): array
    {
        return [
            self::TIPO_CREACION,
            self::TIPO_ELIMINACION,
            self::TIPO_EDICION,
            self::TIPO_REPORTE,
            self::TIPO_CAMBIO_ESTADO,
            self::TIPO_REVISION,
            self::TIPO_NOTIFICACION,
        ];
    }
}
