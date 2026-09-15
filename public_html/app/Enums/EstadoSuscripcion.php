<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estados de una suscripcion mensual. — RN-15
 *
 * Respalda la columna ENUM `suscripciones.estado`.
 */
enum EstadoSuscripcion: string
{
    case ACTIVA = 'ACTIVA';
    case PAUSADA = 'PAUSADA';
    case CANCELADA = 'CANCELADA';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::ACTIVA => 'Activa',
            self::PAUSADA => 'Pausada',
            self::CANCELADA => 'Cancelada',
        };
    }
}
