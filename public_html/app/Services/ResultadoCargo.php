<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPago;

/**
 * Lo que devuelve la pasarela tras intentar un cobro. — RN-21
 *
 * Solo lleva datos que se pueden guardar y mostrar: el identificador del cargo,
 * la marca y los cuatro ultimos digitos. El numero de tarjeta nunca llega al
 * servidor, se queda entre el navegador y Culqi.
 */
final readonly class ResultadoCargo
{
    private function __construct(
        public EstadoPago $estado,
        public ?string $cargoId,
        public ?string $marca,
        public ?string $ultimosCuatro,
        public ?string $mensaje,
    ) {
    }

    public static function aprobado(
        string $cargoId,
        ?string $marca = null,
        ?string $ultimosCuatro = null,
    ): self {
        return new self(EstadoPago::APROBADO, $cargoId, $marca, $ultimosCuatro, null);
    }

    /** La tarjeta existe pero el emisor nego la operacion. */
    public static function rechazado(string $mensaje, ?string $cargoId = null): self
    {
        return new self(EstadoPago::RECHAZADO, $cargoId, null, null, $mensaje);
    }

    /** No se pudo completar el intento: red caida, llave mal configurada, etc. */
    public static function error(string $mensaje): self
    {
        return new self(EstadoPago::ERROR, null, null, null, $mensaje);
    }

    public function fueAprobado(): bool
    {
        return $this->estado === EstadoPago::APROBADO;
    }
}
