<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Como recibe el cliente su pedido.
 *
 * Respalda la columna ENUM `pedidos.modalidad_entrega`.
 */
enum ModalidadEntrega: string
{
    case DELIVERY = 'DELIVERY';
    case RECOJO = 'RECOJO';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::DELIVERY => 'Delivery en Surco',
            self::RECOJO => 'Recojo en tienda',
        };
    }
}
