<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\SuscriptorBoletin;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Confirmacion de alta en el boletin, con el enlace para darse de baja.
 */
final class BienvenidaBoletinMail extends Mailable
{
    public function __construct(public readonly SuscriptorBoletin $suscriptor) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Te suscribiste al boletín de VetPet Surco 🐾');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.boletin', with: [
            'suscriptor' => $this->suscriptor,
            'enlaceBaja' => route('boletin.baja', $this->suscriptor->token_baja),
        ]);
    }
}
