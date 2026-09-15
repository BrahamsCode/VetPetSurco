<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Especie;
use App\Models\Mascota;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mascota>
 */
class MascotaFactory extends Factory
{
    protected $model = Mascota::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cliente_id' => Usuario::factory()->cliente(),
            'nombre' => fake()->firstName(),
            'especie' => fake()->randomElement(Especie::cases()),
            'raza' => fake()->randomElement(['Labrador', 'Mestizo', 'Schnauzer', 'Siames', 'Bulldog frances']),
            'fecha_nacimiento' => fake()->dateTimeBetween('-10 years', '-6 months')->format('Y-m-d'),
            'peso_kg' => fake()->randomFloat(2, 1, 45),
            'alergias' => 'Ninguna conocida',
        ];
    }

    public function perro(): static
    {
        return $this->state(fn (array $atributos) => ['especie' => Especie::PERRO]);
    }

    public function gato(): static
    {
        return $this->state(fn (array $atributos) => ['especie' => Especie::GATO]);
    }
}
