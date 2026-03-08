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

    public function revisionesFinancieras(): HasMany
    {
        return $this->hasMany(RevisionFinanciera::class, 'tipo_solicitud_id');
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
     * Tipos con revisiones financieras
     */
    public function scopeConRevisiones(Builder $query): Builder
    {
        return $query->has('revisionesFinancieras');
    }

    // ---- Accesores ----

    /**
     * Total de revisiones financieras de este tipo
     */
    public function getTotalRevisionesAttribute(): int
    {
        return $this->revisionesFinancieras()->count();
    }
}
