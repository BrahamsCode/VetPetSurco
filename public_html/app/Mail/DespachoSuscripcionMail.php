<?php

namespace App\Mail;

use App\Models\Mascota;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Usuario;
use App\Services\SuscripcionService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Despacho mensual emitido por la suscripcion (RF-03 / RN-14).
 *
 * Se manda cuando `suscripciones:despachar` genera el pedido recurrente:
 * el cliente se entera de que su alimento ya va en camino y de cuando toca
 * el siguiente (RN-16).
 */
final class DespachoSuscripcionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Suscripcion $suscripcion,
        private readonly Pedido $pedido,
        private readonly Usuario $cliente,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu despacho mensual ya salió — Pedido '.$this->pedido->getKey().' | VetPet Surco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.despacho',
            with: [
                'suscripcion' => $this->suscripcion,
                'pedido' => $this->pedido,
                'cliente' => $this->cliente,
                'mascota' => Mascota::query()->find($this->suscripcion->mascota_id),
                'producto' => Producto::query()->find($this->suscripcion->producto_id),
                'unidades' => SuscripcionService::unidadesDe((string) $this->suscripcion->plan),
            ],
        );
    }
}
