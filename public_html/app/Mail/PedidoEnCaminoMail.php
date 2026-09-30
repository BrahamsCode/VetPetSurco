<?php

namespace App\Mail;

use App\Enums\ModalidadEntrega;
use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * El pedido paso a ENVIADO: va en camino (delivery) o esta listo para
 * recoger en la tienda (recojo).
 */
final class PedidoEnCaminoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Pedido $pedido,
    ) {
    }

    public function envelope(): Envelope
    {
        $asunto = $this->pedido->modalidad() === ModalidadEntrega::DELIVERY
            ? 'Tu pedido '.$this->pedido->getKey().' va en camino'
            : 'Tu pedido '.$this->pedido->getKey().' está listo para recoger';

        return new Envelope(subject: $asunto.' | VetPet Surco');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.entrega',
            with: ['pedido' => $this->pedido],
        );
    }
}
