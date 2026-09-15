<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Origen del pedido: venta de mostrador o despacho de una suscripcion. — RN-14
 *
 * Respalda la columna ENUM `pedidos.tipo_origen`.
 */
enum TipoOrigen: string
{
    case COMPRA_DIRECTA = 'COMPRA_DIRECTA';
    case SUSCRIPCION = 'SUSCRIPCION';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::COMPRA_DIRECTA => 'Compra directa',
            self::SUSCRIPCION => 'Suscripcion',
        };
    }
}
