<?php

namespace App\Actions\Municipios;

use App\Models\Municipio;
use Illuminate\Support\Facades\Validator;

class ActualizarContactoMunicipioAction
{
    public function validar(array $input): array
    {
        $validated = Validator::make(
            $input,
            [
                'contactoNombre' => 'nullable|string|max:100',
                'contactoEmail' => 'nullable|email|max:255',
                'contactoTelefono' => ['nullable', 'regex:/^[0-9]{8}$/'],
                'observaciones' => 'nullable|string|max:1000',
            ],
            [
                'contactoEmail.email' => 'El correo de contacto debe ser valido.',
                'contactoNombre.max' => 'El nombre de contacto no debe exceder 100 caracteres.',
                'contactoTelefono.regex' => 'El telefono debe tener exactamente 8 digitos numericos.',
                'observaciones.max' => 'Las observaciones no deben exceder 1000 caracteres.',
            ],
        )->validate();

        $validated['contactoNombre'] = isset($validated['contactoNombre']) && $validated['contactoNombre'] !== ''
            ? trim($validated['contactoNombre'])
            : null;
        $validated['contactoEmail'] = isset($validated['contactoEmail']) && $validated['contactoEmail'] !== ''
            ? mb_strtolower(trim($validated['contactoEmail']))
            : null;
        $validated['contactoTelefono'] = isset($validated['contactoTelefono']) && $validated['contactoTelefono'] !== ''
            ? trim($validated['contactoTelefono'])
            : null;
        $validated['observaciones'] = isset($validated['observaciones']) && $validated['observaciones'] !== ''
            ? trim($validated['observaciones'])
            : null;

        return $validated;
    }

    public function ejecutar(int $municipioId, array $validated): ?Municipio
    {
        $municipio = Municipio::find($municipioId);

        if (!$municipio) {
            return null;
        }

        $municipio->update([
            'contacto_nombre' => $validated['contactoNombre'] ?: null,
            'contacto_email' => $validated['contactoEmail'] ?: null,
            'contacto_telefono' => $validated['contactoTelefono'] ?: null,
            'observaciones' => $validated['observaciones'] ?: null,
        ]);

        return $municipio;
    }
}
