<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetallePedido>
 */
class DetallePedidoFactory extends Factory
{
    protected $model = DetallePedido::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 5);
        $precio = fake()->randomFloat(2, 10, 200);

        return [
            'pedido_id' => Pedido::factory(),
            'producto_id' => Producto::factory(),
            // RN-10: la cantidad siempre es mayor que cero.
            'cantidad' => $cantidad,
            // RN-11: precio historico congelado al confirmar.
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
        ];
    }
}
