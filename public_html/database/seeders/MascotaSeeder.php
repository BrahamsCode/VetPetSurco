<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Especie;
use App\Models\Mascota;
use Illuminate\Database\Seeder;

/**
 * Mascotas de los clientes de demostracion. — RN-04
 */
class MascotaSeeder extends Seeder
{
    public function run(): void
    {
        $mascotas = [
            [1, 4, 'Rocky', Especie::PERRO, 'Labrador', '2021-04-12', 28.50, 'Ninguna conocida'],
            [2, 4, 'Michi', Especie::GATO, 'Mestizo', '2023-01-30', 4.20, 'Pollo'],
            [3, 5, 'Luna', Especie::PERRO, 'Schnauzer', '2019-09-05', 8.10, 'Ninguna conocida'],
            [4, 6, 'Simba', Especie::GATO, 'Siames', '2022-06-18', 5.00, 'Ninguna conocida'],
            [5, 6, 'Toby', Especie::PERRO, 'Bulldog frances', '2020-11-22', 12.30, 'Polen'],
        ];

        foreach ($mascotas as [$id, $cliente, $nombre, $especie, $raza, $nacimiento, $peso, $alergias]) {
            Mascota::forceCreate([
                'mascota_id' => $id,
                'cliente_id' => $cliente,
                'nombre' => $nombre,
                'especie' => $especie,
                'raza' => $raza,
                'fecha_nacimiento' => $nacimiento,
                'peso_kg' => $peso,
                'alergias' => $alergias,
            ]);
        }
    }
}
