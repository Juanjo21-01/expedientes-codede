<?php

namespace App\Mail;

use App\Models\NotificacionEnviada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Intentos máximos de envío
     */
    public int $tries = 3;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public NotificacionEnviada $notificacion
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notificacion->asunto,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion',
            with: [
                'notificacion' => $this->notificacion,
                'remitente' => $this->notificacion->remitente,
                'expediente' => $this->notificacion->expediente,
                'municipio' => $this->notificacion->municipio,
                'tipoNotificacion' => $this->notificacion->tipoNotificacion,
            ],
        );
    }
}
