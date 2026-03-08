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
        TipoSolicitud::create(['nombre' => 'Primer Desembolso (20%)']);
        TipoSolicitud::create(['nombre' => 'Segundo Desembolso']);
        TipoSolicitud::create(['nombre' => 'Tercer Desembolso']);
        TipoSolicitud::create(['nombre' => 'Pago Final (100%)']);
    }
}
