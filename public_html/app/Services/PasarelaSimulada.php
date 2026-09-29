<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Pasarela de mentira para la fase de prueba. — RN-21
 *
 * Se usa mientras no haya llaves de Culqi en el `.env`. Imita el contrato de
 * la pasarela real: recibe un token generado en el navegador (el numero de
 * tarjeta nunca llega al servidor) y decide segun los cuatro ultimos digitos,
 * igual que hacen las tarjetas de prueba de cualquier pasarela.
 *
 *   - terminadas en 0000  -> rechazadas por el emisor
 *   - cualquier otra      -> aprobadas
 *
 * El token viaja con el formato `tkn_sim_<marca>_<cuatro digitos>`.
 */
final class PasarelaSimulada implements Pasarela
{
    /** Las tarjetas terminadas en estos digitos simulan un rechazo. */
    private const TERMINACION_RECHAZADA = '0000';

    public function esSimulada(): bool
    {
        return true;
    }

    public function cobrar(int $centimos, string $token, string $correo, string $descripcion): ResultadoCargo
    {
        if (preg_match('/^tkn_sim_([a-z]+)_(\d{4})$/', $token, $partes) !== 1) {
            return ResultadoCargo::error('El token simulado no tiene el formato esperado.');
        }

        [, $marca, $ultimosCuatro] = $partes;

        if ($ultimosCuatro === self::TERMINACION_RECHAZADA) {
            return ResultadoCargo::rechazado(
                'Tu tarjeta fue rechazada por el emisor. Intenta con otra.',
            );
        }

        return ResultadoCargo::aprobado(
            'chr_sim_'.Str::lower(Str::random(16)),
            Str::title($marca),
            $ultimosCuatro,
        );
    }
}
