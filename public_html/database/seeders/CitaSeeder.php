<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EstadoCita;
use App\Enums\Servicio;
use App\Models\Cita;
use Illuminate\Database\Seeder;

/**
 * Agenda de demostracion. — RF-04
 *
 * Dos citas futuras reservadas y dos pasadas ya atendidas. Ningun par
 * (veterinario, fecha_hora) se repite: lo impide `uk_agenda` (RN-17).
 */
class CitaSeeder extends Seeder
{
    public function run(): void
    {
        $citas = [
            [1, 1, 2, Servicio::VACUNACION, 2, 10, EstadoCita::RESERVADA],
            [2, 3, 2, Servicio::CONSULTA, 2, 11, EstadoCita::RESERVADA],
            [3, 4, 3, Servicio::DESPARASITACION, -5, 16, EstadoCita::ATENDIDA],
            [4, 5, 3, Servicio::GROOMING, -9, 9, EstadoCita::ATENDIDA],
        ];

        foreach ($citas as [$id, $mascota, $veterinario, $servicio, $dias, $hora, $estado]) {
            Cita::forceCreate([
                'cita_id' => $id,
                'mascota_id' => $mascota,
                'veterinario_id' => $veterinario,
                'servicio' => $servicio,
                'fecha_hora' => now()->startOfDay()->addDays($dias)->setTime($hora, 0),
                'estado' => $estado,
            ]);
        }
    }
}
