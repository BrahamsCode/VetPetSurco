<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estados de una cita de la agenda. — RN-18
 *
 * Respalda la columna ENUM `citas.estado`.
 */
enum EstadoCita: string
{
    case RESERVADA = 'RESERVADA';
    case ATENDIDA = 'ATENDIDA';
    case CANCELADA = 'CANCELADA';
    case NO_ASISTIO = 'NO_ASISTIO';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::RESERVADA => 'Reservada',
            self::ATENDIDA => 'Atendida',
            self::CANCELADA => 'Cancelada',
            self::NO_ASISTIO => 'No asistio',
        };
    }
}
