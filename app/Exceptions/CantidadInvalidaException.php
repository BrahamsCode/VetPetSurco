<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-10: no se compran cantidades menores o iguales a cero.
 *
 * Duplica en la aplicación lo que el esquema declara como
 * `CHECK (cantidad > 0)` sobre `detalle_pedidos`.
 */
final class CantidadInvalidaException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'La cantidad debe ser un número entero mayor que cero.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-10';
    }
}
