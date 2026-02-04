<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // Nombre de la tabla
    protected $table = 'roles';

    // Atributos asignables
    protected $fillable = ['nombre',];

    // ---- Constantes de Roles ----
    public const ADMIN = 'Administrador';
    public const DIRECTOR = 'Director General';
    public const JEFE_FINANCIERO = 'Jefe Administrativo-Financiero';
    public const TECNICO = 'Técnico';
    public const MUNICIPAL = 'Municipal';

    // Roles que requieren municipios asignados
    public const ROLES_CON_MUNICIPIOS = [self::TECNICO, self::MUNICIPAL];

    // ---- Relaciones ----

    // --> USUARIOS -> Uno a Muchos
    public function users() : HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    // ---- Helpers de Instancia ----

    public function esAdmin(): bool
    {
        return $this->nombre === self::ADMIN;
    }

    public function esDirector(): bool
    {
        return $this->nombre === self::DIRECTOR;
    }

    public function esJefeFinanciero(): bool
    {
        return $this->nombre === self::JEFE_FINANCIERO;
    }

    public function esTecnico(): bool
    {
        return $this->nombre === self::TECNICO;
    }

    public function esMunicipal(): bool
    {
        return $this->nombre === self::MUNICIPAL;
    }

    public function requiereMunicipios(): bool
    {
        return in_array($this->nombre, self::ROLES_CON_MUNICIPIOS);
    }

    public function tieneAccesoGlobal(): bool
    {
        return in_array($this->nombre, [self::ADMIN, self::DIRECTOR, self::JEFE_FINANCIERO]);
    }
}
