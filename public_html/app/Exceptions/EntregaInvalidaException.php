<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RF-02: el pedido tiene que decir como se entrega. Se lanza cuando se pide
 * delivery sin una direccion a la cual llevarlo.
 */
final class EntregaInvalidaException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'Los datos de entrega del pedido no son validos.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RF-02';
    }
}
