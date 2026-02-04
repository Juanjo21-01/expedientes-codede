<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoNotificacion extends Model
{
    // Nombre de la tabla
    protected $table = 'tipo_notificaciones';

    // Atributos asignables
    protected $fillable = ['nombre'];

    // ---- Relaciones ----

    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'tipo_notificacion_id');
    }

    // ---- Scopes ----

    /**
     * Ordenar alfabéticamente
     */
    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('nombre');
    }

    /**
     * Buscar por nombre
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where('nombre', 'like', "%{$termino}%");
    }

    // ---- Accesores ----

    /**
     * Total de notificaciones de este tipo
     */
    public function getTotalNotificacionesAttribute(): int
    {
        return $this->notificaciones()->count();
    }

    /**
     * Notificaciones enviadas de este tipo
     */
    public function getNotificacionesEnviadasAttribute(): int
    {
        return $this->notificaciones()->enviadas()->count();
    }

    /**
     * Notificaciones pendientes de este tipo
     */
    public function getNotificacionesPendientesAttribute(): int
    {
        return $this->notificaciones()->pendientes()->count();
    }
}
