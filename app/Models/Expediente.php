<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Expediente extends Model
{
    // Nombre de la tabla
    protected $table = 'expedientes';

    // Atributos asignables
    protected $fillable = [
        'codigo_snip',
        'nombre_proyecto',
        'municipio_id',
        'responsable_id',
        'tipo_solicitud_id',
        'ordinario_extraordinario',
        'fecha_recibido',
        'estado',
        'fecha_aprobacion',
        'monto_contrato',
        'adjudicatario',
        'observaciones',
        'etiquetas',
        'fecha_ultima_actualizacion',
        'financiero_estado',
        'financiero_revisor_id',
        'financiero_fecha_recepcion',
        'financiero_fecha_revision',
        'financiero_fecha_complemento',
        'financiero_monto_aprobado',
        'financiero_comentarios',
    ];

    // Atributos que deben ser casteados
    protected function casts(): array
    {
        return [
            'etiquetas' => 'array',
            'fecha_recibido' => 'date',
            'fecha_aprobacion' => 'date',
            'fecha_ultima_actualizacion' => 'datetime',
        ];
    }

    // ---- Relaciones ----

    // --> MUNICIPIO -> Muchos a Uno
    public function municipio() : BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    // --> RESPONSABLE (USUARIO) -> Muchos a Uno
    public function responsable() : BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // --> TIPO DE SOLICITUD -> Muchos a Uno
    public function tipoSolicitud() : BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    // --> REVISOR FINANCIERO (USUARIO) -> Muchos a Uno
    public function revisorFinanciero() : BelongsTo
    {
        return $this->belongsTo(User::class, 'financiero_revisor_id');
    }

    // -> REVISIONES FINANCIERAS -> Uno a Muchos
    public function revisionesFinancieras() : HasMany
    {
        return $this->hasMany(RevisionFinanciera::class, 'expediente_id');
    }
    
    // -> NOTIFICACIONES ENVIADAS -> Uno a Muchos
    public function notificacionesEnviadas() : HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'expediente_id');
    }
}
