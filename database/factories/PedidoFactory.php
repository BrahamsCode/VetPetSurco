<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoPedido;
use App\Enums\TipoOrigen;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cliente_id' => Usuario::factory()->cliente(),
            'fecha_pedido' => now(),
            'monto_total' => fake()->randomFloat(2, 20, 500),
            'tipo_origen' => TipoOrigen::COMPRA_DIRECTA,
            'estado' => EstadoPedido::PENDIENTE,
        ];
    }

    /** Pedido nacido de un despacho de suscripcion. — RN-14 */
    public function deSuscripcion(): static
    {
        return $this->state(fn (array $atributos) => ['tipo_origen' => TipoOrigen::SUSCRIPCION]);
    }

    /** Pedido en un estado concreto de la secuencia. — RN-13 */
    public function enEstado(EstadoPedido $estado): static
    {
        return $this->state(fn (array $atributos) => ['estado' => $estado]);
    }
}
