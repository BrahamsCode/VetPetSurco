<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoSuscripcion;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Suscripcion>
 */
class SuscripcionFactory extends Factory
{
    protected $model = Suscripcion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cliente_id' => Usuario::factory()->cliente(),
            // La mascota debe ser del mismo cliente: se crea despues de
            // resolver `cliente_id` para reutilizar ese mismo usuario. — RN-04
            'mascota_id' => fn (array $atributos) => Mascota::factory()
                ->create(['cliente_id' => $atributos['cliente_id']])->mascota_id,
            'producto_id' => Producto::factory(),
            'plan' => fake()->randomElement(['BASICO', 'CUIDADO', 'INTEGRAL']),
            // RN-16: ciclo de despacho en dias.
            'frecuencia_dias' => 30,
            'monto_mensual' => fake()->randomFloat(2, 80, 250),
            'proximo_despacho' => now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'estado' => EstadoSuscripcion::ACTIVA,
        ];
    }

    /** Suscripcion pausada por el cliente. — RN-15 */
    public function pausada(): static
    {
        return $this->state(fn (array $atributos) => ['estado' => EstadoSuscripcion::PAUSADA]);
    }

    /** Suscripcion cancelada. — RN-15 */
    public function cancelada(): static
    {
        return $this->state(fn (array $atributos) => ['estado' => EstadoSuscripcion::CANCELADA]);
    }
}
