<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Comprobante de pago por correo (demo academica).
 *
 * Se envia al cliente cuando el cobro del pedido queda aprobado (RN-21).
 * El correo nunca bloquea la compra: PagoController lo envuelve en try/catch.
 */
final class FacturaPedidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Pedido $pedido,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu comprobante de pago - Pedido '.$this->pedido->getKey().' | VetPet Surco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
            with: ['pedido' => $this->pedido],
        );
    }
}
