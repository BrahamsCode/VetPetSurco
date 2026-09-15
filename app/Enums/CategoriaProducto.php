<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Categorias del catalogo de productos.
 *
 * Respalda la columna ENUM `productos.categoria`.
 */
enum CategoriaProducto: string
{
    case ALIMENTO = 'ALIMENTO';
    case ACCESORIO = 'ACCESORIO';
    case MEDICAMENTO = 'MEDICAMENTO';
    case ARENA = 'ARENA';

    /** Etiqueta legible para las vistas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::ALIMENTO => 'Alimento',
            self::ACCESORIO => 'Accesorio',
            self::MEDICAMENTO => 'Medicamento',
            self::ARENA => 'Arena sanitaria',
        };
    }
}
