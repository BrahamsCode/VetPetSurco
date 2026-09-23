<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mensaje del formulario de contacto: viaja del CLIENTE a la EMPRESA.
 *
 * Llega al buzon de la tienda con la etiqueta de su cola en el asunto
 * (config/correos.php) y con Reply-To del cliente, de modo que responder
 * el correo contesta directo a quien escribio.
 */
final class MensajeContactoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{nombre: string, correo: string, telefono: ?string, mascota: ?string,
     *              motivo: string, motivo_texto: string, mensaje: string} $datos
     */
    public function __construct(
        private readonly array $datos,
    ) {
    }

    public function envelope(): Envelope
    {
        $cola = config('correos.colas_contacto.'.$this->datos['motivo'], '[CONTACTO]');

        return new Envelope(
            subject: $cola.' '.$this->datos['motivo_texto'].' — '.$this->datos['nombre'],
            replyTo: [new Address($this->datos['correo'], $this->datos['nombre'])],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contacto',
            with: ['datos' => $this->datos],
        );
    }
}
