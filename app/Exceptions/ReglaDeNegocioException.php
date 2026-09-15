<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Raíz de todas las excepciones que representan una regla de negocio incumplida.
 *
 * Cada subclase declara el código RN-xx del catálogo (docs/reglas-de-negocio.md),
 * de modo que la capa de presentación pueda pintar el aviso con la misma
 * etiqueta que usan la presentación, el esquema MySQL y el prototipo.
 */
abstract class ReglaDeNegocioException extends RuntimeException
{
    /**
     * Código de la regla incumplida, con el formato «RN-xx».
     */
    abstract public function regla(): string;
}
