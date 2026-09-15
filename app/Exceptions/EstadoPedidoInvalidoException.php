<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-13: el pedido recorre una secuencia de estados definida
 * (PENDIENTE → PAGADO → ENVIADO → ENTREGADO), sin saltos ni vuelta atrás.
 *
 * Se lanza cuando se pide avanzar un pedido que ya está ENTREGADO o que fue
 * ANULADO, los dos casos en los que `EstadoPedido::siguiente()` no devuelve nada.
 */
final class EstadoPedidoInvalidoException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'El pedido no puede avanzar a otro estado.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-13';
    }
}
