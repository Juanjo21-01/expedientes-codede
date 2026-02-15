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
            'Documentación incompleta',
            'Solicitud de corrección',
            'Revisión financiera',
            'Solicitud de información',
            'General',
        ];

        foreach ($tipos as $tipo) {
            TipoNotificacion::firstOrCreate(['nombre' => $tipo]);
        }
    }
}
