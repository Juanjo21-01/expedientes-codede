<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $this->call(RoleSeeder::class);

        // Usuario Administrador
        $this->call(UserAdminSeeder::class);

        // Municipios
        $this->call(MunicipiosSeeder::class);

        // Tipos de Solicitud
        $this->call(TipoSolicitudesSeeder::class);

        // Tipos de Notificación
        $this->call(TipoNotificacionesSeeder::class);
    }
}
