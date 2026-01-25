<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoSolicitud;

class TipoSolicitudesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear tipos de solicitudes
        TipoSolicitud::create(['nombre' => 'Anticipo (20%)']);
        TipoSolicitud::create(['nombre' => 'Primer Desembolso']);
        TipoSolicitud::create(['nombre' => 'Segundo Desembolso (46%)']);
        TipoSolicitud::create(['nombre' => 'Pago Final (100%)']);
    }
}
