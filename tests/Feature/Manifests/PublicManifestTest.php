<?php

namespace Tests\Feature\Manifests;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Manifest;
use App\Models\RpbiProfile;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicManifestTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_public_uuid_returns_not_found(): void
    {
        $this->get('/client-access/manifests/'.Str::uuid())
            ->assertNotFound();
    }

    public function test_public_page_renders_manifest_builder(): void
    {
        $manifest = $this->manifest();

        $this->get('/client-access/manifests/'.$manifest->public_uuid)
            ->assertOk()
            ->assertSee('id="public-manifest"', false)
            ->assertSee('id="manifest-frame"', false)
            ->assertSee('Creando tu manifiesto', false)
            ->assertSee($manifest->public_uuid, false);
    }

    public function test_json_returns_manifest_header_for_the_builder(): void
    {
        $manifest = $this->manifest();

        $this->getJson('/client-access/manifests/'.$manifest->public_uuid)
            ->assertOk()
            ->assertJsonPath('data.folio', $manifest->id)
            ->assertJsonPath('data.driver', 'TEST')
            ->assertJsonPath('data.client.name', 'Clinica Norte')
            ->assertJsonPath('data.client.nra', 'NRA-100')
            ->assertJsonPath('data.details.0.description', 'Sangre humana y sus componentes en forma líquida.');
    }

    private function manifest(): Manifest
    {
        $zone = Zone::factory()->create();
        $client = Client::factory()->create([
            'zone_id' => $zone->id,
            'company' => 'Clinica Norte',
            'nra' => 'NRA-100',
        ]);
        $contract = Contract::factory()->create();
        $service = Service::query()->create([
            'client_id' => $client->id,
            'zone_id' => $zone->id,
            'contract_id' => $contract->id,
            'service_date' => now()->toDateString(),
            'status' => Service::STATUS_PENDING,
        ]);
        $profile = RpbiProfile::query()->create([
            'name' => 'Sangre',
            'code' => 'BI5',
            'description' => 'Sangre humana y sus componentes en forma líquida.',
        ]);
        ServiceDetail::query()->create([
            'service_id' => $service->id,
            'weight' => '12',
            'rpbi_profile_id' => $profile->id,
        ]);

        return Manifest::query()->create([
            'public_uuid' => (string) Str::uuid(),
            'service_id' => $service->id,
            'status' => Manifest::STATUS_PENDING,
        ]);
    }
}
