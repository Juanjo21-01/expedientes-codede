<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Expediente extends Model
{
    // Nombre de la tabla
    protected $table = 'expedientes';

    // ---- Constantes de Estado ----
    public const ESTADO_RECIBIDO = 'Recibido';
    public const ESTADO_EN_REVISION = 'En Revisión';
    public const ESTADO_COMPLETO = 'Completo';
    public const ESTADO_INCOMPLETO = 'Incompleto';
    public const ESTADO_APROBADO = 'Aprobado';
    public const ESTADO_RECHAZADO = 'Rechazado';
    public const ESTADO_ARCHIVADO = 'Archivado';

    // Estados que permiten edición
    public const ESTADOS_EDITABLES = [
        self::ESTADO_RECIBIDO,
        self::ESTADO_EN_REVISION,
        self::ESTADO_INCOMPLETO,
    ];

    // Estados finales (no se puede cambiar)
    public const ESTADOS_FINALES = [
        self::ESTADO_APROBADO,
        self::ESTADO_RECHAZADO,
        self::ESTADO_ARCHIVADO,
    ];

    // ---- Constantes de Tipo ----
    public const TIPO_ORDINARIO = 'ORDINARIO';
    public const TIPO_EXTRAORDINARIO = 'EXTRAORDINARIO';
    public const TIPO_ASIGNACION_EXTRAORDINARIA = 'ASIGNACION EXTRAORDINARIA';

    // Atributos asignables
    protected $fillable = [
        'codigo_snip',
        'nombre_proyecto',
        'municipio_id',
        'responsable_id',
        'tipo_solicitud_id',
        'ordinario_extraordinario',
        'fecha_recibido',
        'estado',
        'fecha_aprobacion',
        'monto_contrato',
        'adjudicatario',
        'observaciones',
        'etiquetas',
    ];

    // Atributos que deben ser casteados
    protected function casts(): array
    {
        return [
            'etiquetas' => 'array',
            'fecha_recibido' => 'date',
            'fecha_aprobacion' => 'date',
            'monto_contrato' => 'decimal:2',
        ];
    }

    // ---- Relaciones ----

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    public function revisionesFinancieras(): HasMany
    {
        return $this->hasMany(RevisionFinanciera::class, 'expediente_id');
    }

    public function notificacionesEnviadas(): HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'expediente_id');
    }

    /**
     * Última revisión financiera
     */
    public function ultimaRevision(): HasOne
    {
        return $this->hasOne(RevisionFinanciera::class, 'expediente_id')->latestOfMany();
    }

    // ---- Scopes ----

    /**
     * Filtrar por estado
     */
    public function scopeDeEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    /**
     * Expedientes recibidos
     */
    public function scopeRecibidos(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_RECIBIDO);
    }

    /**
     * Expedientes en revisión
     */
    public function scopeEnRevision(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_EN_REVISION);
    }

    /**
     * Expedientes completos
     */
    public function scopeCompletos(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_COMPLETO);
    }

    /**
     * Expedientes incompletos
     */
    public function scopeIncompletos(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_INCOMPLETO);
    }

    /**
     * Expedientes aprobados
     */
    public function scopeAprobados(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_APROBADO);
    }

    /**
     * Expedientes rechazados
     */
    public function scopeRechazados(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_RECHAZADO);
    }

    /**
     * Expedientes archivados
     */
    public function scopeArchivados(Builder $query): Builder
    {
        return $query->deEstado(self::ESTADO_ARCHIVADO);
    }

    /**
     * Expedientes activos (no finalizados)
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereNotIn('estado', self::ESTADOS_FINALES);
    }

    /**
     * Expedientes finalizados
     */
    public function scopeFinalizados(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_FINALES);
    }

    /**
     * Filtrar por municipio
     */
    public function scopeDeMunicipio(Builder $query, int $municipioId): Builder
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Filtrar por municipios (array)
     */
    public function scopeDeMunicipios(Builder $query, array $municipioIds): Builder
    {
        return $query->whereIn('municipio_id', $municipioIds);
    }

    /**
     * Filtrar por responsable
     */
    public function scopeDeResponsable(Builder $query, int $userId): Builder
    {
        return $query->where('responsable_id', $userId);
    }

    /**
     * Filtrar por tipo (Ordinario/Extraordinario)
     */
    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('ordinario_extraordinario', $tipo);
    }

    /**
     * Expedientes recibidos en un rango de fechas
     */
    public function scopeRecibidosEntre(Builder $query, $desde, $hasta): Builder
    {
        return $query->whereBetween('fecha_recibido', [$desde, $hasta]);
    }

    /**
     * Expedientes de este año
     */
    public function scopeDeEsteAnio(Builder $query): Builder
    {
        return $query->whereYear('fecha_recibido', now()->year);
    }

    /**
     * Expedientes de este mes
     */
    public function scopeDeEsteMes(Builder $query): Builder
    {
        return $query->whereYear('fecha_recibido', now()->year)
                     ->whereMonth('fecha_recibido', now()->month);
    }

    /**
     * Buscar por código SNIP o nombre
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo_snip', 'like', "%{$termino}%")
              ->orWhere('nombre_proyecto', 'like', "%{$termino}%");
        });
    }

    /**
     * Accesibles por un usuario (según sus municipios)
     */
    public function scopeAccesiblesPor(Builder $query, User $user): Builder
    {
        if ($user->hasGlobalAccess()) {
            return $query;
        }

        return $query->whereIn('municipio_id', $user->municipios_ids);
    }

    // ---- Helpers de Estado ----

    public function estaRecibido(): bool
    {
        return $this->estado === self::ESTADO_RECIBIDO;
    }

    public function estaEnRevision(): bool
    {
        return $this->estado === self::ESTADO_EN_REVISION;
    }

    public function estaCompleto(): bool
    {
        return $this->estado === self::ESTADO_COMPLETO;
    }

    public function estaIncompleto(): bool
    {
        return $this->estado === self::ESTADO_INCOMPLETO;
    }

    public function estaAprobado(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    public function estaRechazado(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADO;
    }

    public function estaArchivado(): bool
    {
        return $this->estado === self::ESTADO_ARCHIVADO;
    }

    public function estaFinalizado(): bool
    {
        return in_array($this->estado, self::ESTADOS_FINALES);
    }

    public function esEditable(): bool
    {
        return in_array($this->estado, self::ESTADOS_EDITABLES);
    }

    // ---- Helpers de Tipo ----

    public function esOrdinario(): bool
    {
        return $this->ordinario_extraordinario === self::TIPO_ORDINARIO;
    }

    public function esExtraordinario(): bool
    {
        return $this->ordinario_extraordinario === self::TIPO_EXTRAORDINARIO;
    }

    public function esAsignacionExtraordinaria(): bool
    {
        return $this->ordinario_extraordinario === self::TIPO_ASIGNACION_EXTRAORDINARIA;
    }

    // ---- Métodos de Cambio de Estado ----

    /**
     * Cambiar estado con validación
     */
    public function cambiarEstado(string $nuevoEstado): bool
    {
        if ($this->estaFinalizado()) {
            return false;
        }

        $this->estado = $nuevoEstado;

        if ($nuevoEstado === self::ESTADO_APROBADO) {
            $this->fecha_aprobacion = now();
        }

        return $this->save();
    }

    public function marcarEnRevision(): bool
    {
        return $this->cambiarEstado(self::ESTADO_EN_REVISION);
    }

    public function marcarCompleto(): bool
    {
        return $this->cambiarEstado(self::ESTADO_COMPLETO);
    }

    public function marcarIncompleto(): bool
    {
        return $this->cambiarEstado(self::ESTADO_INCOMPLETO);
    }

    public function aprobar(): bool
    {
        return $this->cambiarEstado(self::ESTADO_APROBADO);
    }

    public function rechazar(): bool
    {
        return $this->cambiarEstado(self::ESTADO_RECHAZADO);
    }

    public function archivar(): bool
    {
        return $this->cambiarEstado(self::ESTADO_ARCHIVADO);
    }

    // ---- Accesores ----

    /**
     * Días desde que se recibió
     */
    public function getDiasDesdeRecibidoAttribute(): int
    {
        return $this->fecha_recibido->diffInDays(now());
    }

    /**
     * Monto formateado
     */
    public function getMontoFormateadoAttribute(): string
    {
        return $this->monto_contrato 
            ? 'Q ' . number_format($this->monto_contrato, 2) 
            : 'N/A';
    }

    /**
     * Badge del estado con color
     */
    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_RECIBIDO => 'badge-info',
            self::ESTADO_EN_REVISION => 'badge-warning',
            self::ESTADO_COMPLETO => 'badge-success',
            self::ESTADO_INCOMPLETO => 'badge-error',
            self::ESTADO_APROBADO => 'badge-success',
            self::ESTADO_RECHAZADO => 'badge-error',
            self::ESTADO_ARCHIVADO => 'badge-ghost',
            default => 'badge-neutral',
        };
    }

    /**
     * Obtener array de todos los estados para selects
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_RECIBIDO,
            self::ESTADO_EN_REVISION,
            self::ESTADO_COMPLETO,
            self::ESTADO_INCOMPLETO,
            self::ESTADO_APROBADO,
            self::ESTADO_RECHAZADO,
            self::ESTADO_ARCHIVADO,
        ];
    }

    /**
     * Obtener array de tipos para selects
     */
    public static function getTipos(): array
    {
        return [
            self::TIPO_ORDINARIO,
            self::TIPO_EXTRAORDINARIO,
            self::TIPO_ASIGNACION_EXTRAORDINARIA,
        ];
    }
}
