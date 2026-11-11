<?php

namespace App\Features\Manifests\Jobs;

use App\Features\WhatsApp\Application\UseCases\SendWhatsappTemplate;
use App\Features\WhatsApp\Domain\Templates\ClientServiceReminderTemplate;
use App\Models\Manifest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendManifestCreatedWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $manifestId,
    ) {}
    public function handle(SendWhatsappTemplate $sendWhatsappTemplate): void
    {
        // $manifest = Manifest::find($this->manifestId);
        $manifest = Manifest::select([
            'manifests.id',
            'manifests.public_uuid',
            'manifests.service_id',
        ])
            ->with(['service' => function ($query) {
                $query->select('id', 'service_date', 'client_id')
                    ->with(['client' => function ($query) {
                        $query->select('id', 'name', 'parentarl_surname', 'phone');
                    }])
                    ->with(['serviceDetails' => function ($query) {
                        $query->select('id', 'service_id', 'rpbi_profile_id')
                            ->with(['rpbiProfile' => function ($query) {
                                $query->select('id', 'name');
                            }]);
                    }]);
            }])
            ->where('id', $this->manifestId)->first();
        $residues = $manifest->service->serviceDetails->map(function ($serviceDetail) {
            return $serviceDetail->rpbiProfile->name;
        })->implode(', ');


        $to = '6144950659';

        $template = new ClientServiceReminderTemplate(
            $manifest->service->client->name,
            $manifest->service->service_date,
            'BAN-' . $manifest->id,
            $residues,
            $manifest->public_uuid
        );
        $sendWhatsappTemplate->__invoke($to, $template);

        info('Se Envía Whatsapp');
    }
}
