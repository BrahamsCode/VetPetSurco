<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoriaProducto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            // RN-05: el SKU debe ser unico en todo el catalogo.
            'codigo_sku' => strtoupper(fake()->unique()->bothify('???-???-##')),
            'nombre' => 'Producto '.fake()->unique()->word(),
            'categoria' => fake()->randomElement(CategoriaProducto::cases()),
            // RN-06: siempre mayor que cero.
            'precio' => fake()->randomFloat(2, 10, 300),
            // RN-07: nunca negativo.
            'stock_actual' => fake()->numberBetween(0, 60),
            'punto_reorden' => 5,
            'activo' => true,
        ];
    }

    /** Producto por debajo del punto de reorden (semaforo ROJO). — RN-08 */
    public function sinStock(): static
    {
        return $this->state(fn (array $atributos) => ['stock_actual' => 0]);
    }

    /** Producto con existencias holgadas (semaforo VERDE). — RN-08 */
    public function conStock(int $unidades = 50): static
    {
        return $this->state(fn (array $atributos) => ['stock_actual' => $unidades]);
    }

    /** Producto retirado del catalogo. */
    public function inactivo(): static
    {
        return $this->state(fn (array $atributos) => ['activo' => false]);
    }
}
