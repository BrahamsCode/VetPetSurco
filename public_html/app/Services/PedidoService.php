<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPedido;
use App\Enums\TipoOrigen;
use App\Exceptions\CantidadInvalidaException;
use App\Exceptions\EstadoPedidoInvalidoException;
use App\Exceptions\StockInsuficienteException;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * Core transaccional de la compra: es el equivalente en Laravel del
 * procedimiento `sp_confirmar_pedido` (basedatos/03_transaccion_compra.sql).
 *
 * Reglas que hace cumplir:
 *  - RN-12: sin stock suficiente no hay pedido; se revierte todo.
 *  - RN-07: el inventario nunca queda en negativo.
 *  - RN-11: el precio unitario se copia al detalle y ya no cambia.
 *  - RN-13: el pedido recorre la secuencia de estados definida.
 *  - RN-14: se distingue la compra puntual del despacho por suscripción.
 */
final class PedidoService
{
    /**
     * Confirma la compra completa dentro de una sola transacción.
     *
     * Pasos, en el mismo orden que el procedimiento almacenado:
     *  1. Consolida las líneas por producto (RN-09) y valida las cantidades (RN-10).
     *  2. Abre la transacción.
     *  3. Bloquea con `lockForUpdate()` cada fila de producto y acumula TODOS
     *     los faltantes antes de decidir (RN-12).
     *  4. Si falta aunque sea uno, lanza la excepción sin haber escrito nada.
     *  5. Si alcanza, crea el pedido y su detalle copiando el precio unitario
     *     vigente (RN-11) y descuenta el stock (RN-07).
     *
     * @param array<int, array{producto_id: int|string, cantidad: int|string, nombre?: string}> $lineas
     *        Líneas del carrito, tal como las devuelve `CarritoService::items()`.
     *
     * @throws StockInsuficienteException si alguna línea no tiene stock (RN-12)
     * @throws CantidadInvalidaException  si alguna cantidad no es entera y positiva (RN-10)
     */
    public function confirmar(Usuario $cliente, array $lineas, TipoOrigen $origen): Pedido
    {
        // Paso 1: RN-09 — un producto aparece una sola vez por pedido, así que
        // dos líneas del mismo producto se suman antes de tocar la base de datos
        // (la tabla lo impone con UNIQUE KEY uk_linea_pedido).
        $pedidas = $this->consolidar($lineas);

        if ($pedidas === []) {
            // RN-12: sin líneas no hay pedido que registrar.
            throw new StockInsuficienteException(
                [],
                'El carrito está vacío: no hay nada que confirmar.',
            );
        }

        // Paso 2: toda la operación es atómica. Si algo falla —una excepción
        // nuestra o un rechazo del motor— Laravel revierte la transacción
        // completa, igual que el EXIT HANDLER del procedimiento almacenado.
        return DB::transaction(function () use ($cliente, $pedidas, $origen): Pedido {
            $productos    = [];
            $faltantes    = [];
            $totalCentimos = 0;

            // Paso 3: se recorre en orden de identificador para que dos compras
            // simultáneas tomen los bloqueos siempre en la misma secuencia y no
            // se abracen en un interbloqueo.
            foreach ($pedidas as $productoId => $cantidad) {
                /** @var Producto|null $producto */
                $producto = Producto::query()
                    ->whereKey($productoId)
                    ->lockForUpdate()   // RNF-03: la fila queda bloqueada hasta el COMMIT
                    ->first();

                $disponible = $producto === null ? 0 : (int) $producto->stock_actual;

                if ($producto === null || $disponible < $cantidad) {
                    // RN-12: no se corta aquí; se sigue recorriendo para poder
                    // informar de una vez todo lo que falta.
                    $faltantes[] = [
                        'producto_id' => $producto === null ? null : (int) $producto->getKey(),
                        'nombre'      => $producto === null
                            ? 'Producto #' . $productoId . ' no disponible'
                            : (string) $producto->nombre,
                        'pedida'      => $cantidad,
                        'disponible'  => $disponible,
                    ];

                    continue;
                }

                $productos[$productoId] = $producto;
            }

            // Paso 4: decisión única con todos los faltantes reunidos. Hasta
            // aquí no se ha escrito absolutamente nada: solo se leyó con
            // bloqueo, así que basta con lanzar para que todo quede atrás.
            if ($faltantes !== []) {
                throw new StockInsuficienteException($faltantes);
            }

            // Paso 5: el pedido. El monto total se arma en céntimos enteros
            // para que la suma de los subtotales cuadre al céntimo (RN-11).
            foreach ($pedidas as $productoId => $cantidad) {
                $totalCentimos += CarritoService::aCentimos($productos[$productoId]->precio) * $cantidad;
            }

            $pedido = new Pedido();
            $pedido->cliente_id   = $cliente->getKey();
            $pedido->fecha_pedido = now();
            $pedido->monto_total  = CarritoService::aDecimal($totalCentimos);
            $pedido->tipo_origen  = $origen;                 // RN-14
            $pedido->estado       = EstadoPedido::PAGADO;    // RN-13: primer paso de la secuencia
            $pedido->save();

            foreach ($pedidas as $productoId => $cantidad) {
                $producto        = $productos[$productoId];
                $precioCentimos  = CarritoService::aCentimos($producto->precio);

                $detalle = new DetallePedido();
                $detalle->pedido_id   = $pedido->getKey();
                $detalle->producto_id = $producto->getKey();
                $detalle->cantidad    = $cantidad;
                // RN-11: se copia el precio vigente; el detalle es el precio
                // histórico y ya no cambia aunque el catálogo suba mañana.
                $detalle->precio_unitario = CarritoService::aDecimal($precioCentimos);
                $detalle->subtotal        = CarritoService::aDecimal($precioCentimos * $cantidad);
                $detalle->save();

                // Descuento del inventario en tiempo real, sobre la fila que
                // sigue bloqueada desde el paso 3.
                $restante = (int) $producto->stock_actual - $cantidad;

                if ($restante < 0) {
                    // RN-07: segunda barrera en la aplicación. No debería
                    // ocurrir nunca tras el paso 4; si ocurriera, la
                    // transacción se revierte antes de dejar el stock negativo
                    // (el esquema lo repite con CHECK (stock_actual >= 0)).
                    throw new StockInsuficienteException([[
                        'producto_id' => (int) $producto->getKey(),
                        'nombre'      => (string) $producto->nombre,
                        'pedida'      => $cantidad,
                        'disponible'  => (int) $producto->stock_actual,
                    ]]);
                }

                $producto->stock_actual = $restante;
                $producto->save();
            }

            return $pedido;
        });
    }

    /**
     * RN-13: avanza el pedido al siguiente estado de la secuencia
     * PENDIENTE → PAGADO → ENVIADO → ENTREGADO.
     *
     * @throws EstadoPedidoInvalidoException si el pedido está ANULADO o ya fue ENTREGADO
     */
    public function avanzarEstado(Pedido $pedido): Pedido
    {
        $actual = $this->estadoDe($pedido);

        if ($actual === EstadoPedido::ANULADO) {
            throw new EstadoPedidoInvalidoException('Un pedido anulado no puede cambiar de estado.');
        }

        $siguiente = $actual->siguiente();   // RN-13

        if ($siguiente === null) {
            throw new EstadoPedidoInvalidoException('El pedido ya está ENTREGADO.');
        }

        $pedido->estado = $siguiente;
        $pedido->save();

        return $pedido;
    }

    /**
     * Agrupa las líneas por producto sumando las cantidades (RN-09) y valida
     * que cada cantidad sea un entero mayor que cero (RN-10).
     *
     * @param  array<int, array{producto_id: int|string, cantidad: int|string, nombre?: string}> $lineas
     * @return array<int, int> cantidad pedida, indexada por `producto_id`
     *
     * @throws CantidadInvalidaException
     */
    private function consolidar(array $lineas): array
    {
        $pedidas = [];

        foreach ($lineas as $linea) {
            if (! isset($linea['producto_id'], $linea['cantidad'])) {
                continue;
            }

            $productoId = (int) $linea['producto_id'];
            $cantidad   = $linea['cantidad'];

            // RN-10: entero y mayor que cero, igual que CHECK (cantidad > 0).
            if (! is_int($cantidad) && ! (is_string($cantidad) && ctype_digit($cantidad))) {
                throw new CantidadInvalidaException();
            }

            $cantidad = (int) $cantidad;

            if ($cantidad <= 0) {
                throw new CantidadInvalidaException();
            }

            // RN-09: si el producto se repite, se acumula en la misma línea.
            $pedidas[$productoId] = ($pedidas[$productoId] ?? 0) + $cantidad;
        }

        // Orden estable de bloqueo para evitar interbloqueos entre compras
        // simultáneas que comparten productos.
        ksort($pedidas);

        return $pedidas;
    }

    /**
     * Lee el estado del pedido admitiendo tanto el enum casteado por el modelo
     * como el texto crudo de la columna ENUM.
     */
    private function estadoDe(Pedido $pedido): EstadoPedido
    {
        $estado = $pedido->estado;

        return $estado instanceof EstadoPedido
            ? $estado
            : EstadoPedido::from((string) $estado);
    }
}
