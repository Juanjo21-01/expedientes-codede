<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    // Nombre de la tabla
    protected $table = 'bitacoras';

    // Atributos asignables
    protected $fillable = [
        'user_id',
        'entidad',
        'tipo',
        'detalle',
    ];

    // ---- Relaciones ----

    // --> USUARIO -> Muchos a Uno
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
