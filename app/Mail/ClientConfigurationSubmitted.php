<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClientConfigurationSubmitted extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Client $client,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cliente pendiente de aprobación: '.$this->client->fullName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.clients.configuration-submitted',
            with: [
                'client' => $this->client,
                'clientContract' => $this->client->pendingContract,
                'zone' => $this->client->zone,
                'approvalsUrl' => route('approvals.index'),
            ],
        );
    }
}
