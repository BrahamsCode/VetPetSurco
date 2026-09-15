<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-19: cada atención genera un único registro clínico (relación 1 a 1).
 *
 * En la base de datos la impone `historias_clinicas.cita_id UNIQUE`; en la
 * aplicación la comprueba `HistoriaClinicaService::registrar()` antes de
 * escribir, y también al capturar el rechazo del motor.
 */
final class AtencionYaRegistradaException extends ReglaDeNegocioException
{
    public function __construct(
        string $mensaje = 'Esta cita ya tiene su registro clínico. Cada atención genera uno solo.',
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-19';
    }
}
