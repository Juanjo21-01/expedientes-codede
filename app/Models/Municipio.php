<?php

namespace App\Models;

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

    // ---- Relaciones ----

    // -> USUARIOS -> Muchos a Muchos
    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_municipio');
    }

    // --> EXPEDIENTES -> Uno a Muchos
    public function expedientes() : HasMany
    {
        return $this->hasMany(Expediente::class, 'municipio_id');
    }
}
