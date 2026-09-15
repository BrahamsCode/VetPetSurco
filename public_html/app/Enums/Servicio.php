<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Servicios que se pueden reservar en la agenda veterinaria. — RN-17
 *
 * Respalda la columna ENUM `citas.servicio`.
 */
enum Servicio: string
{
    case CONSULTA = 'CONSULTA';
    case VACUNACION = 'VACUNACION';
    case DESPARASITACION = 'DESPARASITACION';
    case GROOMING = 'GROOMING';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::CONSULTA => 'Consulta',
            self::VACUNACION => 'Vacunacion',
            self::DESPARASITACION => 'Desparasitacion',
            self::GROOMING => 'Grooming',
        };
    }
}
