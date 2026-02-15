<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Municipio extends Model
{
    // Nombre de la tabla
    protected $table = 'municipios';

    // Atributos asignables
    protected $fillable = [
        'nombre',
        'departamento',
        'contacto_nombre',
        'contacto_email',
        'contacto_telefono',
        'observaciones',
        'estado',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    // ---- Relaciones ----

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_municipio');
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'municipio_id');
    }

    public function notificacionesEnviadas(): HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'municipio_id');
    }

    // ---- Scopes ----

    /**
     * Municipios activos
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }

    /**
     * Municipios inactivos
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('estado', false);
    }

    /**
     * Ordenar alfabéticamente
     */
    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('nombre');
    }

    /**
     * Filtrar por departamento
     */
    public function scopeDeDepartamento(Builder $query, string $departamento): Builder
    {
        return $query->where('departamento', $departamento);
    }

    /**
     * Buscar por nombre
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where('nombre', 'like', "%{$termino}%");
    }

    /**
     * Municipios con expedientes activos
     */
    public function scopeConExpedientesActivos(Builder $query): Builder
    {
        return $query->whereHas('expedientes', fn($q) => $q->activos());
    }

    // ---- Helpers de Estado ----

    public function estaActivo(): bool
    {
        return $this->estado === true;
    }

    public function estaInactivo(): bool
    {
        return $this->estado === false;
    }

    public function activar(): bool
    {
        return $this->update(['estado' => true]);
    }

    public function desactivar(): bool
    {
        return $this->update(['estado' => false]);
    }

    // ---- Helpers de Contacto ----

    /**
     * Verifica si tiene información de contacto completa
     */
    public function tieneContactoCompleto(): bool
    {
        return !empty($this->contacto_nombre)
            && !empty($this->contacto_email)
            && !empty($this->contacto_telefono);
    }

    /**
     * Verifica si tiene email de contacto
     */
    public function tieneEmailContacto(): bool
    {
        return !empty($this->contacto_email);
    }

    // ---- Accesores ----

    /**
     * Nombre completo con departamento
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->departamento
            ? "{$this->nombre}, {$this->departamento}"
            : $this->nombre;
    }

    /**
     * Total de expedientes
     */
    public function getTotalExpedientesAttribute(): int
    {
        return $this->expedientes()->count();
    }

    /**
     * Total de expedientes activos
     */
    public function getExpedientesActivosAttribute(): int
    {
        return $this->expedientes()->activos()->count();
    }

    // ---- Helpers ----

    /**
     * Usuario Municipal asignado a este municipio
     */
    public function getUsuarioMunicipal(): ?User
    {
        return $this->users()
            ->whereHas('role', fn($q) => $q->where('nombre', Role::MUNICIPAL))
            ->first();
    }

    /**
     * Técnicos asignados a este municipio
     */
    public function getTecnicosAsignados()
    {
        return $this->users()
            ->whereHas('role', fn($q) => $q->where('nombre', Role::TECNICO))
            ->get();
    }
}
