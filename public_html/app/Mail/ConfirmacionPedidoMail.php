<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Pedido registrado: queda PENDIENTE hasta que se pague (RN-21). */
final class ConfirmacionPedidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Pedido $pedido,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu pedido '.$this->pedido->getKey().' quedó registrado | VetPet Surco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedido',
            with: ['pedido' => $this->pedido],
        );
    }
}
