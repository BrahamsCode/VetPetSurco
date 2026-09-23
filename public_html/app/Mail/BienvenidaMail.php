<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Bienvenida al crear una cuenta de cliente desde el registro publico. */
final class BienvenidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Usuario $usuario,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a VetPet Surco, '.$this->usuario->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida',
            with: ['usuario' => $this->usuario],
        );
    }
}
