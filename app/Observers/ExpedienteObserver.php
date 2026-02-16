<?php

namespace App\Observers;

use App\Models\Expediente;
use App\Models\Bitacora;

class ExpedienteObserver
{
    /**
     * Handle the Expediente "created" event.
     */
    public function created(Expediente $expediente): void
    {
        $municipio = $expediente->municipio?->nombre ?? 'Sin municipio';

        Bitacora::registrarCreacionExpediente(
            "Expediente {$expediente->codigo_snip} creado – Proyecto: {$expediente->nombre_proyecto}, Municipio: {$municipio}, Estado: {$expediente->estado}",
            $expediente->id
        );
    }

    /**
     * Handle the Expediente "updated" event.
     */
    public function updated(Expediente $expediente): void
    {
        // Si cambió el estado → registrar como cambio de estado
        if ($expediente->isDirty('estado')) {
            $estadoAnterior = $expediente->getOriginal('estado');
            $estadoNuevo = $expediente->estado;

            Bitacora::registrarCambioEstadoExpediente(
                "Expediente {$expediente->codigo_snip} cambió de estado: {$estadoAnterior} → {$estadoNuevo}",
                $expediente->id
            );
            return;
        }

        // Si cambió cualquier otro campo → registrar como edición
        $camposModificados = array_keys($expediente->getDirty());

        // Excluir timestamps de la edición
        $camposModificados = array_diff($camposModificados, ['updated_at', 'created_at']);

        if (!empty($camposModificados)) {
            Bitacora::registrarEdicionExpediente(
                "Expediente {$expediente->codigo_snip} actualizado – Campos: " . implode(', ', $camposModificados),
                $expediente->id
            );
        }
    }

    /**
     * Handle the Expediente "deleted" event.
     */
    public function deleted(Expediente $expediente): void
    {
        Bitacora::registrarEliminacionExpediente(
            "Expediente {$expediente->codigo_snip} eliminado – Proyecto: {$expediente->nombre_proyecto}",
            $expediente->id
        );
    }
}
