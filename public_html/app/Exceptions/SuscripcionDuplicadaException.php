<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-22: una mascota tiene un solo plan vigente (ACTIVO o PAUSADO). Un
 * segundo plan le duplicaria el despacho y el cobro mensual.
 *
 * Para cambiar de plan hay que cancelar el vigente y contratar otro (RN-15).
 */
final class SuscripcionDuplicadaException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'Esa mascota ya tiene un plan vigente. Para cambiarlo, cancela el actual y contrata el nuevo.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-22';
    }
}
