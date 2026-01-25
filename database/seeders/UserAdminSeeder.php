<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asignar el rol de administrador al usuario principal
        $adminRole = Role::where('nombre', 'Administrador/Director')->first();

        // Crear el usuario administrador si no existe
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'nombres' => env('ADMIN_NOMBRES'),
                'apellidos' => env('ADMIN_APELLIDOS'),
                'cargo' => env('ADMIN_CARGO'),
                'telefono' => env('ADMIN_TELEFONO'),
                'password' => bcrypt(env('ADMIN_PASSWORD')),
                'estado' => true,
                'role_id' => $adminRole->id,
            ]
        );
    }
}
