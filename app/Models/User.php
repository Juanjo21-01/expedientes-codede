<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        ];
    }

    // Accesores y Mutadores
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // ---- Relaciones ----

    // --> ROLES -> Muchos a Uno
    public function role() : BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // -> MUNICIPIOS -> Muchos a Muchos
    public function municipios() : BelongsToMany
    {
        return $this->belongsToMany(Municipio::class, 'usuario_municipio');
    }

    // -> EXPEDIENTES (responsable_id) -> Uno a Muchos
    public function expedientes() : HasMany
    {
        return $this->hasMany(Expediente::class, 'responsable_id');
    }

    // -> EXPEDIENTES (financiero_revisor_id) -> Uno a Muchos
    public function expedientesRevisados() : HasMany
    {
        return $this->hasMany(Expediente::class, 'financiero_revisor_id');
    }

    // -> REVISIONES FINANCIERAS -> Uno a Muchos
    public function revisionesFinancieras() : HasMany
    {
        return $this->hasMany(RevisionFinanciera::class, 'revisor_id');
    }
    
    // -> BITACORAS -> Uno a Muchos
    public function bitacoras() : HasMany
    {
        return $this->hasMany(Bitacora::class, 'user_id');
    }
}
