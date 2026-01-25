<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoSolicitud extends Model
{
    // Nombre de la tabla
    protected $table = 'tipo_solicitudes';

    // Atributos asignables
    protected $fillable = ['nombre'];

    // ---- Relaciones ----

    // --> EXPEDIENTES -> Uno a Muchos
    public function expedientes() : HasMany
    {
        return $this->hasMany(Expediente::class, 'tipo_solicitud_id');
    }
}
