<?php

namespace App\Features\Manifests\Jobs;

use App\Models\Manifest;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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

        if (Manifest::where('service_id', $serviceId)->exists()) {
            return;
        }
        $manifest = new Manifest();
        $manifest->public_uuid = Str::uuid()->toString();

        $manifest->service_id = $serviceId;
        $manifest->save();
    }
}
