<?php

namespace App\Mail;

use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Suscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Cambio de estado de la suscripcion: pausar, reanudar o cancelar (RN-15). */
final class CambioSuscripcionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Suscripcion $suscripcion,
        private readonly string $nuevoEstado,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu suscripción quedó '.$this->nuevoEstado.' | VetPet Surco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suscripcion',
            with: [
                'suscripcion' => $this->suscripcion,
                'nuevoEstado' => $this->nuevoEstado,
                'mascota' => Mascota::query()->find($this->suscripcion->mascota_id),
                'producto' => Producto::query()->find($this->suscripcion->producto_id),
            ],
        );
    }
}
