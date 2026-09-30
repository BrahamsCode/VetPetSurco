<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envio de correos de la plataforma.
 *
 * Un fallo del correo nunca bloquea el flujo que lo origino (compra, cita,
 * registro): se registra en el log con report() y el usuario sigue adelante.
 */
final class CorreoService
{
    public function enviar(string $destino, Mailable $correo): void
    {
        try {
            Mail::to($destino)->send($correo);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
