<?php

namespace App\Observers;

use App\Models\Guia;
use App\Models\Bitacora;

class GuiaObserver
{
    /**
     * Handle the Guia "created" event.
     */
    public function created(Guia $guia): void
    {
        Bitacora::registrarCreacionGuia(
            "Guía '{$guia->titulo}' creada – Categoría: {$guia->categoria}, Versión: {$guia->version}",
            $guia->id
        );
    }

    /**
     * Handle the Guia "updated" event.
     */
    public function updated(Guia $guia): void
    {
        // Si cambió el estado (activar/desactivar)
        if ($guia->isDirty('estado')) {
            $nuevoEstado = $guia->estado ? 'Activada' : 'Desactivada';

            Bitacora::registrarCambioEstadoGuia(
                "Guía '{$guia->titulo}' fue {$nuevoEstado} – Categoría: {$guia->categoria}",
                $guia->id
            );
            return;
        }

        // Edición general
        $camposModificados = array_keys($guia->getDirty());
        $camposModificados = array_diff($camposModificados, ['updated_at', 'created_at']);

        if (!empty($camposModificados)) {
            Bitacora::registrarEdicionGuia(
                "Guía '{$guia->titulo}' actualizada – Campos: " . implode(', ', $camposModificados),
                $guia->id
            );
        }
    }

    /**
     * Handle the Guia "deleted" event.
     */
    public function deleted(Guia $guia): void
    {
        Bitacora::registrarEliminacionGuia(
            "Guía '{$guia->titulo}' eliminada – Categoría: {$guia->categoria}, Versión: {$guia->version}",
            $guia->id
        );
    }
}
