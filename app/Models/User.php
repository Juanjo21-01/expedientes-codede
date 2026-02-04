<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable, TwoFactorAuthenticatable;

    // Nombre de la tabla
    protected $table = 'users';

    // Atributos asignables
    protected $fillable = [
        'nombres',
        'apellidos',
        'cargo',
        'telefono',
        'email',
        'password',
        'estado',
        'role_id',
    ];

    // Atributos ocultos para arrays
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    // Atributos que deben ser casteados
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => 'boolean',
        ];
    }

    // ---- Accesores ----

    /**
     * Nombre completo del usuario
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Iniciales del usuario (para avatares)
     */
    public function getInicialesAttribute(): string
    {
        return strtoupper(
            Str::substr($this->nombres, 0, 1) .
            Str::substr($this->apellidos, 0, 1)
        );
    }

    /**
     * Método legacy para compatibilidad
     */
    public function initials(): string
    {
        return $this->iniciales;
    }

    // ---- Relaciones ----

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Municipios activos asignados al usuario
     */
    public function municipios(): BelongsToMany
    {
        return $this->belongsToMany(Municipio::class, 'usuario_municipio')
            ->withPivot('estado', 'created_at', 'updated_at')
            ->wherePivot('estado', true);
    }

    /**
     * Todos los municipios asignados (incluyendo inactivos) - para historial
     */
    public function todosMunicipios(): BelongsToMany
    {
        return $this->belongsToMany(Municipio::class, 'usuario_municipio')
            ->withPivot('estado', 'created_at', 'updated_at');
    }

    /**
     * Municipios inactivos (historial)
     */
    public function municipiosHistorial(): BelongsToMany
    {
        return $this->belongsToMany(Municipio::class, 'usuario_municipio')
            ->withPivot('estado', 'created_at', 'updated_at')
            ->wherePivot('estado', false);
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'responsable_id');
    }

    public function revisionesFinancieras(): HasMany
    {
        return $this->hasMany(RevisionFinanciera::class, 'revisor_id');
    }

    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class, 'user_id');
    }

    // ---- Scopes ----

    /**
     * Usuarios activos
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }

    /**
     * Usuarios inactivos
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('estado', false);
    }

    /**
     * Usuarios por rol
     */
    public function scopeDeRol(Builder $query, string $nombreRol): Builder
    {
        return $query->whereHas('role', fn($q) => $q->where('nombre', $nombreRol));
    }

    /**
     * Usuarios que tienen asignado un municipio específico
     */
    public function scopeConMunicipio(Builder $query, int $municipioId): Builder
    {
        return $query->whereHas('municipios', fn($q) => $q->where('municipios.id', $municipioId));
    }

    /**
     * Solo técnicos
     */
    public function scopeTecnicos(Builder $query): Builder
    {
        return $query->deRol(Role::TECNICO);
    }

    /**
     * Solo municipales
     */
    public function scopeMunicipales(Builder $query): Builder
    {
        return $query->deRol(Role::MUNICIPAL);
    }

    // ---- Helpers de Rol (usando constantes de Role) ----

    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role->nombre, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->role->esAdmin();
    }

    public function isDirector(): bool
    {
        return $this->role->esDirector();
    }

    public function isJefeFinanciero(): bool
    {
        return $this->role->esJefeFinanciero();
    }

    public function isTecnico(): bool
    {
        return $this->role->esTecnico();
    }

    public function isMunicipal(): bool
    {
        return $this->role->esMunicipal();
    }

    public function hasGlobalAccess(): bool
    {
        return $this->role->tieneAccesoGlobal();
    }

    public function requiereMunicipios(): bool
    {
        return $this->role->requiereMunicipios();
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

    // ---- Helpers de Municipios ----

    /**
     * Verifica si el usuario tiene acceso a un municipio específico
     */
    public function tieneAccesoAMunicipio(int $municipioId): bool
    {
        // Acceso global = acceso a todos los municipios
        if ($this->hasGlobalAccess()) {
            return true;
        }

        return $this->municipios()->where('municipios.id', $municipioId)->exists();
    }

    /**
     * Obtiene los IDs de municipios a los que tiene acceso
     */
    public function getMunicipiosIdsAttribute(): array
    {
        if ($this->hasGlobalAccess()) {
            return Municipio::pluck('id')->toArray();
        }

        return $this->municipios->pluck('id')->toArray();
    }

    /**
     * Sincroniza municipios con soft delete (mantiene historial)
     * - Desactiva los municipios que ya no están en la lista
     * - Activa o crea los municipios que están en la lista
     */
    public function syncMunicipiosConHistorial(array $municipioIds): void
    {
        $municipioIds = array_map('intval', $municipioIds);
        
        // Obtener IDs actuales activos
        $actualesActivos = $this->municipios()->pluck('municipios.id')->toArray();
        
        // Municipios a desactivar (estaban activos pero ya no están en la nueva lista)
        $aDesactivar = array_diff($actualesActivos, $municipioIds);
        
        // Municipios a activar/crear
        $aActivar = $municipioIds;

        // Desactivar los que ya no están
        if (!empty($aDesactivar)) {
            DB::table('usuario_municipio')
                ->where('user_id', $this->id)
                ->whereIn('municipio_id', $aDesactivar)
                ->update(['estado' => false, 'updated_at' => now()]);
        }

        // Activar o crear los nuevos
        foreach ($aActivar as $municipioId) {
            DB::table('usuario_municipio')
                ->updateOrInsert(
                    ['user_id' => $this->id, 'municipio_id' => $municipioId],
                    ['estado' => true, 'updated_at' => now(), 'created_at' => now()]
                );
        }
    }

    /**
     * Desactiva todos los municipios del usuario (soft delete)
     */
    public function desactivarTodosMunicipios(): void
    {
        DB::table('usuario_municipio')
            ->where('user_id', $this->id)
            ->update(['estado' => false, 'updated_at' => now()]);
    }

    /**
     * Verifica si el usuario tiene municipios asignados (activos o en historial)
     */
    public function tieneMunicipiosEnHistorial(): bool
    {
        return $this->todosMunicipios()->exists();
    }
}
