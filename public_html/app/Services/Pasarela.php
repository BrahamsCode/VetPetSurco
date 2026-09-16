<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Pasarela de pagos del carrito. — RN-21
 *
 * Hay dos implementaciones: la real contra Culqi y una simulada que permite
 * demostrar el flujo mientras no haya llaves. `AppServiceProvider` elige una
 * u otra segun el `.env`, y el resto del proyecto no nota la diferencia.
 */
interface Pasarela
{
    /**
     * Cobra un token de tarjeta generado en el navegador.
     *
     * @param int    $centimos Monto en centimos enteros.
     * @param string $token    Token de tarjeta; el numero nunca llega aqui.
     */
    public function cobrar(int $centimos, string $token, string $correo, string $descripcion): ResultadoCargo;

    /** Si es un cobro de verdad o una simulacion, para avisarlo en pantalla. */
    public function esSimulada(): bool;
}
