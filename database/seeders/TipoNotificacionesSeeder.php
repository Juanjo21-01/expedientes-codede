<?php

namespace Database\Seeders;

use App\Models\TipoNotificacion;
use Illuminate\Database\Seeder;

class TipoNotificacionesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tipos = [
            'Documentación Incompleta',
            'Solicitud de Corrección',
            'Revisión Financiera',
            'Solicitud de Información',
            'General',
        ];

        foreach ($tipos as $tipo) {
            TipoNotificacion::firstOrCreate(['nombre' => $tipo]);
        }
    }
}
