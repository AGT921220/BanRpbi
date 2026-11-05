<?php

namespace App\Features\Manifests\Jobs;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CreateInitialServiceManifestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $clientId;

    public function __construct(int $clientId)
    {
        $this->clientId = $clientId;
    }

    public function handle(): void
    {
        $through = Carbon::now()->addDay()->toDateString();
        $services = Service::query()
            ->whereDate('service_date', '<=', $through)
            ->where('client_id', $this->clientId)
            ->get();
        foreach ($services as $service) {
            CreateManifestJob::dispatch($service->id);
        }
    }
}
