<?php

namespace App\Features\Manifests\Jobs;

use App\Models\Manifest;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CreateManifestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $serviceId,
    ) {}
    public function handle(): void
    {
        $this->createManifestForService($this->serviceId);
        info('Se Envía Whatsapp');
    }

    private function createManifestForService(int $serviceId)
    {
        // Implement the logic to create a manifest for the service
        // This could involve creating a new Manifest model and associating it with the service
        $manifest = new Manifest();
        $manifest->service_id = $serviceId;
        $manifest->save();
    }
}
