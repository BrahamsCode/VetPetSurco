<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-21: un pedido solo pasa a PAGADO si la pasarela aprueba el cobro.
 *
 * Se lanza cuando Culqi rechaza la tarjeta o cuando el cobro no se pudo
 * completar. El pedido se queda en PENDIENTE y el intento queda registrado
 * en `pagos` para que el cliente pueda reintentar con otra tarjeta.
 */
final class PagoRechazadoException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'La pasarela rechazo el pago.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-21';
    }
}
