<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-18: toda cita registra su desenlace, y lo registra una sola vez.
 *
 * Se lanza cuando se intenta cerrar una cita que ya tiene desenlace, o cuando
 * el desenlace propuesto no cierra nada (RESERVADA es el estado de partida,
 * no un final).
 */
final class DesenlaceInvalidoException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'La cita ya tiene un desenlace registrado.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-18';
    }
}
