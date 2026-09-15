<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Roles de acceso de la plataforma. — RN-01
 *
 * Respalda la columna ENUM `usuarios.rol` y el middleware `EnsureRol`.
 */
enum Rol: string
{
    case CLIENTE = 'CLIENTE';
    case VETERINARIO = 'VETERINARIO';
    case ADMIN = 'ADMIN';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::CLIENTE => 'Cliente',
            self::VETERINARIO => 'Veterinario',
            self::ADMIN => 'Administrador',
        };
    }
}
