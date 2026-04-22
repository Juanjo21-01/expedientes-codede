<?php

namespace App\Actions\Guias;

use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class GuardarGuiaAction
{
    public function validar(array $input, bool $modoEdicion, bool $esNuevaCategoria): array
    {
        $rules = [
            'titulo' => 'required|string|max:100',
            'fecha_publicacion' => 'required|date',
        ];

        if (!$modoEdicion) {
            $rules['archivo_pdf'] = 'required|file|mimes:pdf|max:10240';

            if ($esNuevaCategoria) {
                $rules['nuevaCategoria'] = 'required|string|max:100';
            } else {
                $rules['categoriaSeleccionada'] = 'required|string';
            }
        } elseif (!empty($input['archivo_pdf'])) {
            $rules['archivo_pdf'] = 'file|mimes:pdf|max:10240';
        }

        return Validator::make($input, $rules, [
            'titulo.required' => 'El titulo es obligatorio.',
            'titulo.max' => 'El titulo no puede exceder 100 caracteres.',
            'archivo_pdf.required' => 'Debe seleccionar un archivo PDF.',
            'archivo_pdf.mimes' => 'Solo se permiten archivos PDF.',
            'archivo_pdf.max' => 'El archivo no puede exceder 10 MB.',
            'fecha_publicacion.required' => 'La fecha de publicacion es obligatoria.',
            'categoriaSeleccionada.required' => 'Debe seleccionar una categoria.',
            'nuevaCategoria.required' => 'Debe escribir el nombre de la nueva categoria.',
        ])->validate();
    }

    public function guardarEdicion(int $guiaId, array $validated): Guia
    {
        $guia = Guia::findOrFail($guiaId);

        $guia->titulo = $validated['titulo'];
        $guia->fecha_publicacion = $validated['fecha_publicacion'];

        if (!empty($validated['archivo_pdf'])) {
            $guia->eliminarArchivo();
            $nombreArchivo = Guia::generarNombreArchivo($guia->categoria);
            $validated['archivo_pdf']->storeAs('guia', $nombreArchivo, 's3');
            $guia->archivo_pdf = $nombreArchivo;
        }

        $guia->save();

        return $guia;
    }

    public function guardarNueva(array $validated, string $categoria, int $userId): Guia
    {
        if (!Guia::puedeAgregarVersion($categoria) && Guia::contarVersiones($categoria) > 0) {
            throw ValidationException::withMessages([
                'categoriaSeleccionada' => "La categoria '{$categoria}' ya tiene el maximo de " . Guia::MAX_VERSIONES_POR_CATEGORIA . ' versiones.',
            ]);
        }

        $version = Guia::siguienteVersion($categoria);
        $nombreArchivo = Guia::generarNombreArchivo($categoria);

        $validated['archivo_pdf']->storeAs('guia', $nombreArchivo, 's3');

        try {
            return DB::transaction(function () use ($validated, $categoria, $userId, $version, $nombreArchivo): Guia {
                Guia::desactivarCategoria($categoria);

                return Guia::create([
                    'titulo' => $validated['titulo'],
                    'archivo_pdf' => $nombreArchivo,
                    'version' => $version,
                    'categoria' => $categoria,
                    'estado' => true,
                    'fecha_publicacion' => $validated['fecha_publicacion'],
                    'user_id' => $userId,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('s3')->delete('guia/' . $nombreArchivo);
            throw $e;
        }
    }
}
