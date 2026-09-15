<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EstadoPedido;
use App\Enums\TipoOrigen;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Database\Seeder;

/**
 * Pedidos de demostracion con su detalle. — RF-02 / RF-03
 *
 * Cubre los dos origenes (RN-14) y tres estados distintos de la secuencia
 * PENDIENTE -> PAGADO -> ENVIADO -> ENTREGADO (RN-13).
 */
class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $pedidos = [
            [1, 4, 229.80, TipoOrigen::COMPRA_DIRECTA, EstadoPedido::ENTREGADO],
            [2, 5, 189.90, TipoOrigen::SUSCRIPCION, EstadoPedido::ENVIADO],
            [3, 6, 74.80, TipoOrigen::COMPRA_DIRECTA, EstadoPedido::PAGADO],
        ];

        foreach ($pedidos as [$id, $cliente, $monto, $origen, $estado]) {
            Pedido::forceCreate([
                'pedido_id' => $id,
                'cliente_id' => $cliente,
                'fecha_pedido' => now(),
                'monto_total' => $monto,
                'tipo_origen' => $origen,
                'estado' => $estado,
            ]);
        }

        // RN-09: cada producto aparece una sola vez por pedido (uk_linea_pedido).
        // RN-10: todas las cantidades son mayores que cero.
        // RN-11: `precio_unitario` es el precio historico de la compra.
        $lineas = [
            [1, 1, 1, 189.90, 189.90],
            [1, 8, 2, 19.90, 39.80],
            [2, 1, 1, 189.90, 189.90],
            [3, 5, 1, 39.90, 39.90],
            [3, 9, 1, 34.90, 34.90],
        ];

        foreach ($lineas as [$pedido, $producto, $cantidad, $precio, $subtotal]) {
            DetallePedido::forceCreate([
                'pedido_id' => $pedido,
                'producto_id' => $producto,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
            ]);
        }
    }
}
