<?php

namespace App\Actions\Expedientes;

use App\Models\Expediente;
use Illuminate\Support\Facades\Validator;

class GuardarExpedienteAction
{
    public function validar(array $input, bool $modoEdicion, ?int $expedienteId = null): array
    {
        $rules = [
            'codigo_snip' => 'required|string|max:50' . ($modoEdicion ? '|unique:expedientes,codigo_snip,' . $expedienteId : '|unique:expedientes,codigo_snip'),
            'nombre_proyecto' => 'required|string|max:255',
            'municipio_id' => 'required|exists:municipios,id',
            'responsable_id' => 'required|exists:users,id',
            'tipo_asignacion' => 'required|in:ORDINARIO,EXTRAORDINARIO',
            'fecha_recibido' => 'required|date',
            'monto_contrato' => 'nullable|numeric|min:0|max:999999999999.99',
            'aporte_municipalidad' => 'nullable|numeric|min:0|max:999999999999.99',
            'observaciones' => 'nullable|string|max:1000',
        ];

        $messages = [
            'codigo_snip.required' => 'El codigo SNIP es obligatorio.',
            'codigo_snip.unique' => 'Este codigo SNIP ya esta registrado.',
            'nombre_proyecto.required' => 'El nombre del proyecto es obligatorio.',
            'municipio_id.required' => 'Debes seleccionar un municipio.',
            'responsable_id.required' => 'Debes seleccionar un responsable.',
            'tipo_asignacion.required' => 'Debes seleccionar el tipo de asignacion.',
            'fecha_recibido.required' => 'La fecha de recibido es obligatoria.',
            'monto_contrato.numeric' => 'El monto del contrato debe ser un numero valido.',
            'aporte_municipalidad.numeric' => 'El aporte de la municipalidad debe ser un numero valido.',
            'observaciones.max' => 'Las observaciones no deben exceder 1000 caracteres.',
        ];

        return Validator::make($input, $rules, $messages)->validate();
    }

    public function ejecutar(array $validated, bool $modoEdicion, ?int $expedienteId = null): ?Expediente
    {
        $payload = [
            'codigo_snip' => $validated['codigo_snip'],
            'nombre_proyecto' => $validated['nombre_proyecto'],
            'municipio_id' => $validated['municipio_id'],
            'responsable_id' => $validated['responsable_id'],
            'tipo_asignacion' => $validated['tipo_asignacion'],
            'fecha_recibido' => $validated['fecha_recibido'],
            'monto_contrato' => ($validated['monto_contrato'] ?? '') !== '' ? $validated['monto_contrato'] : null,
            'aporte_municipalidad' => ($validated['aporte_municipalidad'] ?? '') !== '' ? $validated['aporte_municipalidad'] : null,
            'observaciones' => ($validated['observaciones'] ?? '') !== '' ? trim($validated['observaciones']) : null,
        ];

        if ($modoEdicion) {
            $expediente = Expediente::find($expedienteId);
            if (!$expediente) {
                return null;
            }

            $expediente->update($payload);

            return $expediente;
        }

        $payload['estado'] = Expediente::ESTADO_RECIBIDO;

        return Expediente::create($payload);
    }
}
