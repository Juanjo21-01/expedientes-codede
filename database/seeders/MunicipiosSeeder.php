<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Municipio;

class MunicipiosSeeder extends Seeder
{
    // Nombre de los municipios
    private $municipios = [
        'San Marcos',
        'San Pedro Sacatepéquez',
        'San Antonio Sacatepéquez',
        'Comitancillo',
        'San Miguel Ixtahuacán',
        'Concepción Tutuapa',
        'Tacaná',
        'Sibinal',
        'Tajumulco',
        'Tejutla',
        'San Rafael Pie de la Cuesta',
        'Nuevo Progreso',
        'El Tumbador',
        'San José El Rodeo',
        'Malacatán',
        'Catarina',
        'Ayutla',
        'Ocós',
        'San Pablo',
        'El Quetzal',
        'La Reforma',
        'Pajapita',
        'Ixchiguán',
        'San José Ojetenam',
        'San Cristóbal Cucho',
        'Sipacapa',
        'Esquipulas Palo Gordo',
        'Río Blanco',
        'San Lorenzo',
        'La Blanca',
    ];


    public function run(): void
    {
        // Crear municipios
        foreach ($this->municipios as $nombre) {
            Municipio::create(['nombre' => $nombre]);
        }
    }
}
