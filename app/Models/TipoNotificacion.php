<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoNotificacion extends Model
{
    // Nombre de la tabla
    protected $table = 'tipo_notificaciones';

    // Atributos asignables
    protected $fillable = ['nombre'];

    // ---- Relaciones ----

    // --> NOTIFICACIONES -> Uno a Muchos
    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'tipo_notificacion_id');
    }
}
