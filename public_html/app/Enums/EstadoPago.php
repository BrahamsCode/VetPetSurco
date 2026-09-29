<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Resultado de un intento de cobro contra la pasarela. — RN-21
 *
 * Respalda la columna ENUM `pagos.estado`. Un pedido puede acumular varios
 * intentos: el cliente reintenta con otra tarjeta y cada intento deja rastro.
 */
enum EstadoPago: string
{
    case APROBADO = 'APROBADO';
    case RECHAZADO = 'RECHAZADO';
    case ERROR = 'ERROR';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::APROBADO => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
            self::ERROR => 'Error de conexion',
        };
    }
}
