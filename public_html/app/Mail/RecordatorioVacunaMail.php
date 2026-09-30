<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Recordatorio de vacuna o control — RN-20 (aviso con 15 dias de antelacion).
 *
 * Recibe la fila que arma HistoriaClinicaService::recordatorios() con los
 * datos de la mascota, el cliente y la fecha proxima.
 */
final class RecordatorioVacunaMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{mascota: string, cliente: string, vacuna_aplicada: string|null, proxima_fecha: string, dias_restantes: int} $recordatorio */
    public function __construct(
        private readonly array $recordatorio,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Faltan '.$this->recordatorio['dias_restantes'].' días para el control de '
                .$this->recordatorio['mascota'].' | VetPet Surco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio',
            with: ['recordatorio' => $this->recordatorio],
        );
    }
}
