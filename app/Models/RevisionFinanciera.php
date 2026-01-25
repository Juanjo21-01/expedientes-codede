<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionFinanciera extends Model
{
    // Nombre de la tabla
    protected $table = 'revisiones_financieras';

    // Atributos asignables
    protected $fillable = [
        'expediente_id',
        'revisor_id',
        'estado',
        'accion',
        'monto_aprobado',
        'observaciones',
        'fecha_revision',
        'fecha_complemento',
        'dias_transcurridos',
    ];

    // ---- Relaciones ----

    // --> EXPEDIENTE -> Muchos a Uno
    public function expediente() : BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    // --> REVISOR (USUARIO) -> Muchos a Uno
    public function revisor() : BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }
}
