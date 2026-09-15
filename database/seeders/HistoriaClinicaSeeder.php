<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HistoriaClinica;
use Illuminate\Database\Seeder;

/**
 * Historias clinicas de las citas ya atendidas. — RF-05 / RN-19 / RN-20
 *
 * La tercera no nace de una cita (`cita_id` null): registra un control
 * historico cargado a mano. Las tres tienen `proxima_fecha` para que el
 * panel de recordatorios muestre datos.
 */
class HistoriaClinicaSeeder extends Seeder
{
    public function run(): void
    {
        $historias = [
            [1, 4, 3, 'Paciente sano, peso adecuado para la edad.', 'Desparasitacion interna via oral.', null, 10],
            [2, 5, 4, 'Dermatitis leve por alergia estacional.', 'Bano medicado semanal por 3 semanas.', null, 7],
            [3, 1, null, 'Control anual de rutina.', 'Refuerzo de vacuna quintuple.', 'Quintuple canina', 30],
        ];

        foreach ($historias as [$id, $mascota, $cita, $diagnostico, $tratamiento, $vacuna, $dias]) {
            HistoriaClinica::forceCreate([
                'historia_id' => $id,
                'mascota_id' => $mascota,
                // RN-19: cita_id es unico, una sola historia por atencion.
                'cita_id' => $cita,
                'fecha_atencion' => now(),
                'diagnostico' => $diagnostico,
                'tratamiento' => $tratamiento,
                'vacuna_aplicada' => $vacuna,
                // RN-20: fecha del proximo control.
                'proxima_fecha' => now()->addDays($dias)->toDateString(),
            ]);
        }
    }
}
