<?php

namespace App\Actions\Revisiones;

use App\Models\Bitacora;
use App\Models\Expediente;
use App\Models\RevisionFinanciera;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegistrarRevisionFinancieraAction
{
    public function validar(array $input, ?float $montoRestante = null): array
    {
        $rules = [
            'tipo_solicitud_id' => 'required|exists:tipo_solicitudes,id',
            'estado' => 'required|in:' . implode(',', RevisionFinanciera::getEstados()),
            'observaciones' => 'required|string|max:2000',
            'accion' => 'required|in:' . implode(',', RevisionFinanciera::getAcciones()),
        ];

        if (($input['estado'] ?? null) === RevisionFinanciera::ESTADO_COMPLETO) {
            $maxMonto = $montoRestante ?? 999999999;
            $rules['monto_aprobado'] = "required|numeric|min:0.01|max:{$maxMonto}";
        } elseif (($input['monto_aprobado'] ?? '') !== '') {
            $rules['monto_aprobado'] = 'numeric|min:0';
        }

        return Validator::make($input, $rules, [
            'tipo_solicitud_id.required' => 'La fase de desembolso es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es valido.',
            'observaciones.required' => 'Las observaciones son obligatorias.',
            'observaciones.max' => 'Las observaciones no pueden exceder 2000 caracteres.',
            'accion.required' => 'La accion es obligatoria.',
            'accion.in' => 'La accion seleccionada no es valida.',
            'monto_aprobado.required' => 'El monto aprobado es obligatorio para fases completas.',
            'monto_aprobado.numeric' => 'El monto debe ser un numero valido.',
            'monto_aprobado.min' => 'El monto debe ser mayor a 0.',
            'monto_aprobado.max' => 'El monto excede el restante disponible.',
        ])->validate();
    }

    public function ejecutar(Expediente $expediente, User $revisor, array $validated, int $numeroRevision): RevisionFinanciera
    {
        return DB::transaction(function () use ($expediente, $revisor, $validated, $numeroRevision): RevisionFinanciera {
            $revision = RevisionFinanciera::create([
                'expediente_id' => $expediente->id,
                'tipo_solicitud_id' => $validated['tipo_solicitud_id'],
                'numero_revision' => $numeroRevision,
                'revisor_id' => $revisor->id,
                'estado' => $validated['estado'],
                'accion' => $validated['accion'] ?? null,
                'monto_aprobado' => ($validated['monto_aprobado'] ?? '') !== '' ? $validated['monto_aprobado'] : null,
                'observaciones' => $validated['observaciones'],
                'fecha_revision' => now(),
            ]);

            $tiposSolicitudCount = TipoSolicitud::count();
            $fasesCompletadasDespues = $expediente->fresh()
                ->revisionesFinancieras()
                ->where('estado', RevisionFinanciera::ESTADO_COMPLETO)
                ->distinct('tipo_solicitud_id')
                ->count('tipo_solicitud_id');

            if ($fasesCompletadasDespues >= $tiposSolicitudCount) {
                $expediente->aprobar();
            }

            $fase = TipoSolicitud::find($validated['tipo_solicitud_id']);
            $faseNombre = $fase ? $fase->nombre : 'N/A';
            $montoTexto = !empty($validated['monto_aprobado'])
                ? ' - Monto: Q' . number_format((float) $validated['monto_aprobado'], 2)
                : '';

            Bitacora::registrarRevision(
                "Revision #{$numeroRevision} de {$faseNombre} en Expediente {$expediente->codigo_snip} - Estado: {$validated['estado']}{$montoTexto}",
                $expediente->id,
            );

            return $revision;
        });
    }
}
