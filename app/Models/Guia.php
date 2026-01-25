<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    // Nombre de la tabla
    protected $table = 'guias';

    // Atributos asignables
    protected $fillable = [
        'titulo',
        'archivo_pdf',
        'version',
        'fecha_publicacion',
    ];
}
