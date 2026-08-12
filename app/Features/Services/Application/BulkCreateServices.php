<?php

namespace App\Features\Services\Application;

use App\Models\Client;
use App\Models\Contract;
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
        $frequency = $contract->frequency;
        foreach ($this->serviceDateGenerator->__invoke($clientContract->start_date, $clientContract->end_date, $frequency) as $serviceDate) {
            $serviceId = $this->createServiceForContract($client, $contract, $serviceDate);createServiceDetails
            $this->createServiceDetails($serviceId);
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
    private function createServiceDetails(int $serviceId)
    {
        $dataToInsert = [];
        
    }
}
