<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

/**
 * Raíz de todas las excepciones que representan una regla de negocio incumplida.
 *
 * Cada subclase declara el código RN-xx del catálogo (docs/reglas-de-negocio.md),
 * de modo que la capa de presentación pueda pintar el aviso con la misma
 * etiqueta que usan la presentación, el esquema MySQL y el prototipo.
 *
 * Extiende `DomainException` porque una regla incumplida es un caso previsto
 * del dominio, no un fallo técnico: así un `catch` genérico de `DomainException`
 * también las recoge.
 */
abstract class ReglaDeNegocioException extends DomainException
{
    /**
     * Código de la regla incumplida, con el formato «RN-xx».
     */
    abstract public function regla(): string;
}
