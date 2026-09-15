<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistoriaClinica>
 */
class HistoriaClinicaFactory extends Factory
{
    protected $model = HistoriaClinica::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'mascota_id' => Mascota::factory(),
            // RN-19: `cita_id` es unico; null cuando la historia no nace de una cita.
            'cita_id' => null,
            'fecha_atencion' => now(),
            'diagnostico' => 'Control de rutina sin hallazgos.',
            'tratamiento' => 'Sin tratamiento indicado.',
            'vacuna_aplicada' => null,
            // RN-20: alimenta el recordatorio de salud.
            'proxima_fecha' => now()->addDays(fake()->numberBetween(1, 60))->toDateString(),
        ];
    }

    /** Historia enlazada a una cita concreta. — RN-19 */
    public function deCita(Cita $cita): static
    {
        return $this->state(fn (array $atributos) => [
            'cita_id' => $cita->cita_id,
            'mascota_id' => $cita->mascota_id,
        ]);
    }

    /** Historia con recordatorio dentro de los proximos dias. — RN-20 */
    public function conRecordatorio(int $dias = 7): static
    {
        return $this->state(fn (array $atributos) => [
            'proxima_fecha' => now()->addDays($dias)->toDateString(),
        ]);
    }
}
