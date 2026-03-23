<?php

namespace App\Actions\Usuarios;

use App\Models\Municipio;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class GuardarUsuarioAction
{
    public function validarBase(array $input, ?int $usuarioId): array
    {
        $rules = [
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'telefono' => ['nullable', 'size:8', 'regex:/^[0-9]+$/'],
            'email' => 'required|email|max:255|unique:users,email,' . $usuarioId,
            'roleId' => 'required|exists:roles,id',
            'password' => $usuarioId ? 'nullable|string|min:8' : 'required|string|min:8',
        ];

        $validated = Validator::make($input, $rules, [
            'nombres.required' => 'Los nombres son requeridos.',
            'apellidos.required' => 'Los apellidos son requeridos.',
            'email.required' => 'El correo es requerido.',
            'email.email' => 'El correo debe ser valido.',
            'email.unique' => 'El correo ya esta registrado.',
            'roleId.required' => 'Debe seleccionar un rol.',
            'telefono.size' => 'El telefono debe tener exactamente 8 digitos.',
            'telefono.regex' => 'El telefono solo puede contener numeros.',
            'password.required' => 'La contrasena es requerida.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ])->validate();

        $validated['nombres'] = trim($validated['nombres']);
        $validated['apellidos'] = trim($validated['apellidos']);
        $validated['cargo'] = isset($validated['cargo']) && $validated['cargo'] !== '' ? trim($validated['cargo']) : null;
        $validated['telefono'] = isset($validated['telefono']) && $validated['telefono'] !== '' ? trim($validated['telefono']) : null;
        $validated['email'] = mb_strtolower(trim($validated['email']));

        return $validated;
    }

    public function validarMunicipiosSegunRol(?Role $rol, array $municipiosSeleccionados, ?int $municipioSeleccionado, ?int $usuarioId): array
    {
        if (!$rol || !$rol->requiereMunicipios()) {
            return ['ok' => true];
        }

        if ($rol->esMunicipal()) {
            if (!$municipioSeleccionado) {
                return ['ok' => false, 'field' => 'municipioSeleccionado', 'message' => 'Debe seleccionar un municipio.'];
            }

            $existeMunicipal = DB::table('usuario_municipio')
                ->join('users', 'users.id', '=', 'usuario_municipio.user_id')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->where('roles.nombre', Role::MUNICIPAL)
                ->where('users.estado', true)
                ->where('usuario_municipio.estado', true)
                ->where('usuario_municipio.municipio_id', $municipioSeleccionado)
                ->when($usuarioId, fn($q) => $q->where('users.id', '!=', $usuarioId))
                ->exists();

            if ($existeMunicipal) {
                return ['ok' => false, 'field' => 'municipioSeleccionado', 'message' => 'Ya existe un usuario Municipal activo asignado a este municipio.'];
            }

            return ['ok' => true, 'municipios' => [(int) $municipioSeleccionado]];
        }

        if ($rol->esTecnico()) {
            if (empty($municipiosSeleccionados)) {
                return ['ok' => false, 'field' => 'municipiosSeleccionados', 'message' => 'Debe seleccionar al menos un municipio.'];
            }

            $municipiosOcupadosPorOtro = DB::table('usuario_municipio')
                ->join('users', 'users.id', '=', 'usuario_municipio.user_id')
                ->join('roles', 'roles.id', '=', 'users.role_id')
                ->where('roles.nombre', Role::TECNICO)
                ->where('users.estado', true)
                ->where('usuario_municipio.estado', true)
                ->whereIn('usuario_municipio.municipio_id', $municipiosSeleccionados)
                ->when($usuarioId, fn($q) => $q->where('users.id', '!=', $usuarioId))
                ->pluck('usuario_municipio.municipio_id')
                ->toArray();

            if (!empty($municipiosOcupadosPorOtro)) {
                $nombresMunicipios = Municipio::whereIn('id', $municipiosOcupadosPorOtro)->pluck('nombre')->join(', ');
                return ['ok' => false, 'field' => 'municipiosSeleccionados', 'message' => "Los siguientes municipios ya estan asignados a otro Tecnico activo: {$nombresMunicipios}"];
            }
        }

        return ['ok' => true, 'municipios' => array_map('intval', $municipiosSeleccionados)];
    }

    public function ejecutar(array $validated, ?int $usuarioId, ?Role $rolSeleccionado, array $municipiosSeleccionados): ?User
    {
        return DB::transaction(function () use ($validated, $usuarioId, $rolSeleccionado, $municipiosSeleccionados): ?User {
            if ($usuarioId) {
                $usuario = User::find($usuarioId);
                if (!$usuario) {
                    return null;
                }

                $usuario->nombres = $validated['nombres'];
                $usuario->apellidos = $validated['apellidos'];
                $usuario->cargo = $validated['cargo'] ?? null;
                $usuario->telefono = $validated['telefono'] ?? null;
                $usuario->email = $validated['email'];

                if (!empty($validated['password'])) {
                    $usuario->password = Hash::make($validated['password']);
                }

                $usuario->save();

                if ($rolSeleccionado?->requiereMunicipios()) {
                    $usuario->syncMunicipiosConHistorial($municipiosSeleccionados);
                } else {
                    $usuario->desactivarTodosMunicipios();
                }

                return $usuario;
            }

            $usuario = User::create([
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'cargo' => $validated['cargo'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['roleId'],
            ]);

            if ($rolSeleccionado?->requiereMunicipios()) {
                $usuario->syncMunicipiosConHistorial($municipiosSeleccionados);
            }

            return $usuario;
        });
    }
}
