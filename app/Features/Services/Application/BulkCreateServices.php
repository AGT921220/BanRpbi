<?php

namespace App\Features\Services\Application;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Manifest;
use App\Models\Service;
use Illuminate\Support\Carbon;

class BulkCreateServices
{
    private ServiceDateGenerator $serviceDateGenerator;
    public function __construct(ServiceDateGenerator $serviceDateGenerator)
    {
        $this->serviceDateGenerator = $serviceDateGenerator;

    }
    public function __invoke(Client $client): void
    {
        $clientContract = $client->activeContract;
        $contract = $clientContract->contract;
        $clientContractProfiles = $clientContract->clientContractProfiles;
        $clientProfileIds = $clientContractProfiles->pluck('rpbi_profile_id')->toArray();
        $frequency = $contract->frequency;
        foreach ($this->serviceDateGenerator->__invoke($clientContract->start_date, $clientContract->end_date, $frequency) as $serviceDate) {
            $serviceId = $this->createServiceForContract($client, $contract, $serviceDate);
            $this->createServiceDetails($serviceId, $clientProfileIds);
            $this->createManifestForService($serviceId);
        }
    }
    private function createServiceForContract(Client $client, Contract $contract, Carbon $serviceDate): int
    {
        $service = new Service();
        $service->service_date = $serviceDate;
        $service->contract_id = $contract->id;
        $service->client_id = $client->id;
        $service->status = Service::STATUS_PENDING;
        $service->zone_id = $client->zone_id;
        $service->save();
        return $service->id;
    }
    private function createServiceDetails(int $serviceId, array $clientProfileIds)
    {
        $dataToInsert = [];
        $now = Carbon::now()->toDateTimeString();
        foreach ($clientProfileIds as $profileId) {
            $dataToInsert[] = [
                'service_id' => $serviceId,
                'rpbi_profile_id' => $profileId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
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
