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

    // ---- Relaciones ----

    // --> USUARIOS -> Uno a Muchos
    public function users() : HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
