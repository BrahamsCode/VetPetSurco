<?php

namespace App\Mail;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Cita reservada en la agenda (RN-17). */
final class ConfirmacionCitaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Cita $cita,
        private readonly Mascota $mascota,
        private readonly Usuario $veterinario,
        private readonly Usuario $cliente,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cita está confirmada | VetPet Surco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cita',
            with: [
                'cita' => $this->cita,
                'mascota' => $this->mascota,
                'veterinario' => $this->veterinario,
                'cliente' => $this->cliente,
            ],
        );
    }
}
