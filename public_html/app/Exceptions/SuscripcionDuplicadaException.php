<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-22: una mascota no puede tener dos suscripciones vigentes del mismo
 * producto, porque recibiria el mismo despacho dos veces y se le cobraria
 * dos veces al mes.
 *
 * Para cambiar de plan hay que cancelar el vigente y contratar otro (RN-15).
 */
final class SuscripcionDuplicadaException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'Esa mascota ya tiene una suscripcion vigente de ese producto.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-22';
    }
}
