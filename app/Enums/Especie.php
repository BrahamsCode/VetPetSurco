<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Especie de la mascota registrada por el cliente.
 *
 * Respalda la columna ENUM `mascotas.especie`.
 */
enum Especie: string
{
    case PERRO = 'PERRO';
    case GATO = 'GATO';
    case OTRO = 'OTRO';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::PERRO => 'Perro',
            self::GATO => 'Gato',
            self::OTRO => 'Otro',
        };
    }
}
