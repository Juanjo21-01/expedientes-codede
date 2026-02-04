<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoSolicitud extends Model
{
    // Nombre de la tabla
    protected $table = 'tipo_solicitudes';

    // Atributos asignables
    protected $fillable = ['nombre'];

    // ---- Relaciones ----

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'tipo_solicitud_id');
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

    /**
     * Tipos con expedientes
     */
    public function scopeConExpedientes(Builder $query): Builder
    {
        return $query->has('expedientes');
    }

    // ---- Accesores ----

    /**
     * Total de expedientes de este tipo
     */
    public function getTotalExpedientesAttribute(): int
    {
        return $this->expedientes()->count();
    }

    /**
     * Expedientes activos de este tipo
     */
    public function getExpedientesActivosAttribute(): int
    {
        return $this->expedientes()->activos()->count();
    }
}
