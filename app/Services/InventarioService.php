<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Semaforo;
use App\Models\Producto;
use Illuminate\Support\Collection;

/**
 * Control de inventario del catálogo.
 *
 * RN-08: el semáforo decide cuándo reponer un producto. Es la misma regla que
 * la vista `v_semaforo_inventario` del esquema, escrita aquí para que el panel
 * del Administrador no dependa de una vista de MySQL.
 */
final class InventarioService
{
    /**
     * RN-08: semáforo de un producto.
     *
     *  - ROJO  cuando el stock ya llegó al punto de reorden o lo pasó.
     *  - ÁMBAR cuando queda por debajo del doble del punto de reorden.
     *  - VERDE en el resto de los casos.
     */
    public function semaforo(Producto $p): Semaforo
    {
        $stock  = (int) $p->stock_actual;
        $reorden = (int) $p->punto_reorden;

        if ($stock <= $reorden) {
            return Semaforo::ROJO;
        }

        if ($stock <= $reorden * 2) {
            return Semaforo::AMBAR;
        }

        return Semaforo::VERDE;
    }

    /**
     * Productos activos que están en ROJO, es decir, la alerta de reposición
     * que el procedimiento `sp_confirmar_pedido` devuelve tras cada compra.
     *
     * @return Collection<int, Producto>
     */
    public function porReponer(): Collection
    {
        return Producto::query()
            ->where('activo', true)
            // RN-08: ROJO es exactamente stock_actual <= punto_reorden.
            ->whereColumn('stock_actual', '<=', 'punto_reorden')
            ->orderBy('stock_actual')
            ->get();
    }
}
