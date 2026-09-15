<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estados por los que pasa un pedido. — RN-13
 *
 * Respalda la columna ENUM `pedidos.estado`.
 */
enum EstadoPedido: string
{
    case PENDIENTE = 'PENDIENTE';
    case PAGADO = 'PAGADO';
    case ENVIADO = 'ENVIADO';
    case ENTREGADO = 'ENTREGADO';
    case ANULADO = 'ANULADO';

    /**
     * Siguiente estado de la secuencia
     * PENDIENTE -> PAGADO -> ENVIADO -> ENTREGADO. — RN-13
     *
     * Devuelve null cuando ya no hay avance posible (ENTREGADO y ANULADO).
     */
    public function siguiente(): ?self
    {
        return match ($this) {
            self::PENDIENTE => self::PAGADO,
            self::PAGADO => self::ENVIADO,
            self::ENVIADO => self::ENTREGADO,
            self::ENTREGADO, self::ANULADO => null,
        };
    }

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::PAGADO => 'Pagado',
            self::ENVIADO => 'Enviado',
            self::ENTREGADO => 'Entregado',
            self::ANULADO => 'Anulado',
        };
    }
}
