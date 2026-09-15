<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-17: un veterinario no atiende dos citas a la misma hora.
 *
 * Se lanza en dos momentos distintos y a propósito:
 *  1. Cuando `AgendaService::reservar()` detecta el choque antes de insertar.
 *  2. Cuando la restricción UNIQUE `uk_agenda (veterinario_id, fecha_hora)`
 *     rechaza la inserción porque otra reserva se adelantó entre la
 *     comprobación y el INSERT. La barrera final siempre es la base de datos.
 */
final class HorarioOcupadoException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'Ese horario ya está reservado con el mismo veterinario.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-17';
    }
}
