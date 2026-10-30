<?php

namespace Tests\Feature\Services;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Driver;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ServiceHeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<string>  $permissions
     */
    private function actingAsUserWithPermissions(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    public function test_index_shows_driver_column(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::COLLECTIONS_VIEW]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Chofer', false);
    }

    public function test_headers_include_driver_name_or_unassigned_label(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::COLLECTIONS_VIEW]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = Contract::factory()->create();
        $driver = Driver::factory()->create(['name' => 'Luis']);
        $date = now()->toDateString();

        $assigned = Service::query()->create([
            'service_date' => $date,
            'zone_id' => $zone->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'driver_id' => $driver->id,
            'status' => Service::STATUS_PENDING,
        ]);
        $unassigned = Service::query()->create([
            'service_date' => $date,
            'zone_id' => $zone->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'status' => Service::STATUS_PENDING,
        ]);

        $response = $this->getJson(route('service-headers.index', [
            'draw' => 1,
            'offset' => 0,
            'limit' => 10,
            'order_by' => 'services.id',
            'order_direction' => 'desc',
        ]));

        $response->assertOk();

        $rows = collect($response->json('data'))->keyBy('id');

        $this->assertSame('Luis', $rows[$assigned->id]['driver_name']);
        $this->assertSame('Sin Chofer Asignado', $rows[$unassigned->id]['driver_name']);
        $this->assertNull($rows[$unassigned->id]['driver_id']);
    }
}
