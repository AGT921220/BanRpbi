<?php

namespace Tests\Feature\Services;

use App\Features\Services\Application\ListServicesForAssignment;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListServicesForAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_hides_services_without_manifest(): void
    {
        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = Contract::factory()->create();
        $date = now()->toDateString();

        Service::query()->create([
            'client_id' => $client->id,
            'zone_id' => $zone->id,
            'contract_id' => $contract->id,
            'service_date' => $date,
            'status' => Service::STATUS_PENDING,
        ]);

        $withManifest = Service::query()->create([
            'client_id' => $client->id,
            'zone_id' => $zone->id,
            'contract_id' => $contract->id,
            'service_date' => $date,
            'status' => Service::STATUS_PENDING,
        ]);

        Manifest::query()->create([
            'service_id' => $withManifest->id,
            'status' => Manifest::STATUS_PENDING,
            'public_uuid' => (string) Str::uuid(),
        ]);

        $result = app(ListServicesForAssignment::class)($date);

        $this->assertCount(1, $result);
        $this->assertSame($withManifest->id, $result->first()->id);
        $this->assertNotNull($result->first()->manifests->first());
    }
}
