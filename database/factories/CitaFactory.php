<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoCita;
use App\Enums\Servicio;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    protected $model = Cita::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'mascota_id' => Mascota::factory(),
            'veterinario_id' => Usuario::factory()->veterinario(),
            'servicio' => fake()->randomElement(Servicio::cases()),
            // RN-17: el par (veterinario_id, fecha_hora) es unico, asi que las
            // horas se reparten para no chocar entre si.
            'fecha_hora' => now()->addDays(fake()->numberBetween(1, 20))
                ->setTime(fake()->numberBetween(8, 18), fake()->randomElement([0, 30]), 0),
            'estado' => EstadoCita::RESERVADA,
        ];
    }

    /** Cita ya cerrada por el veterinario. — RN-18 */
    public function atendida(): static
    {
        return $this->state(fn (array $atributos) => ['estado' => EstadoCita::ATENDIDA]);
    }

    /** Cita cancelada. — RN-18 */
    public function cancelada(): static
    {
        return $this->state(fn (array $atributos) => ['estado' => EstadoCita::CANCELADA]);
    }
}
