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
use Illuminate\Support\Str;

class CreateDailyManifestsJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    public function __invoke(): void
    {
        $now = Carbon::now()->addDay(1)->toDateString();
        $services = Service::
        //whereDate('service_date', $now)->
        get();
        foreach ($services as $service) {
            CreateManifestJob::dispatch($service->id);
        }
    }
    private function createManifestForService(int $serviceId)
    {
        // Implement the logic to create a manifest for the service
        // This could involve creating a new Manifest model and associating it with the service
        $manifest = new Manifest();
        info(Str::uuid()->toString());
        $manifest->public_uuid = Str::uuid()->toString();
        info('manifest');
        info($manifest->toArray());
        $manifest->service_id = $serviceId;
        $manifest->save();
    }
}
