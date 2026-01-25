<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionEnviada extends Model
{
    // Nombre de la tabla
    protected $table = 'notificaciones_enviadas';

    // Atributos asignables
    protected $fillable = [
        'tipo_notificacion_id',
        'expediente_id',
        'destinatario_email',
        'mensaje',
        'enviado_at',
        'estado',
    ];

    // ---- Relaciones ----

    // --> TIPO NOTIFICACION -> Muchos a Uno
    public function tipoNotificacion() : BelongsTo
    {
        return $this->belongsTo(TipoNotificacion::class, 'tipo_notificacion_id');
    }

    // --> EXPEDIENTE -> Muchos a Uno
    public function expediente() : BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}
