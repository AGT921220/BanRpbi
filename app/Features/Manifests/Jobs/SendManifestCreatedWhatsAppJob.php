<?php

namespace App\Features\Manifests\Jobs;

use App\Features\WhatsApp\Domain\Templates\ClientServiceReminderTemplate;
use App\Models\Manifest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendManifestCreatedWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $manifestId,
    ) {}
    public function handle(): void
    {
        $manifest = Manifest::find($this->manifestId);


        $to = '6144950659';

        $template = new ClientServiceReminderTemplate(
            'Alfredo',
            '2022-10-01',
            'Alfredo Chofer',
            'BAN-12345',
            'Cultivos y cepas, Objetos punzocortantes',
            $manifest->public_uuid
        );
        info('Se Envía Whatsapp');
    }
}
