<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        Role::create(['nombre' => 'Administrador']);
        Role::create(['nombre' => 'Director General']);
        Role::create(['nombre' => 'Jefe Administrativo-Financiero']);
        Role::create(['nombre' => 'Técnico']);
        Role::create(['nombre' => 'Municipal']);
    }
}
