<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Semaforo de inventario. — RN-08
 *
 * No corresponde a ninguna columna: lo calcula `InventarioService::semaforo()`
 * a partir de `stock_actual` y `punto_reorden` (equivale a la vista
 * `v_semaforo_inventario` del esquema original).
 */
enum Semaforo: string
{
    case ROJO = 'ROJO';
    case AMBAR = 'AMBAR';
    case VERDE = 'VERDE';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::ROJO => 'Reponer ya',
            self::AMBAR => 'Stock ajustado',
            self::VERDE => 'Stock holgado',
        };
    }
}
