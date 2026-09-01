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

class CreateDailyManifestsJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    public function __invoke(): void
    {
        info('createDailyManifestsJob invoked');
        $now = Carbon::now()->addDay(1)->toDateString();
        info('Current date: '.$now);
        $services = Service::whereDate('service_date', $now)->get();
        info('Services to create manifests for: '.count($services));
        info('Se envía a crear manifiestos');
        // Logic to create daily manifests
        foreach($services as $service){
            CreateManifestJob::dispatch($service->id);
        }
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
