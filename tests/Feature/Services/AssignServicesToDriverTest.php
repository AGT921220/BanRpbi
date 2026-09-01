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

class AssignServicesToDriverTest extends TestCase
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

    private function createService(array $attributes = []): Service
    {
        return Service::query()->create(array_merge([
            'service_date' => now()->toDateString(),
            'zone_id' => Zone::factory()->create()->id,
            'client_id' => Client::factory()->create()->id,
            'contract_id' => Contract::factory()->create()->id,
            'status' => Service::STATUS_PENDING,
        ], $attributes));
    }

    public function test_authorized_user_can_view_assign_page(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::COLLECTIONS_ASSIGN]);

        $service = $this->createService();
        $driver = Driver::factory()->create();

        $response = $this->get(route('services.assign'));

        $response->assertOk();
        $response->assertSee('Asignar recolecciones');
        $response->assertSee('#'.$service->id, false);
        $response->assertSee($driver->fullName());
    }

    public function test_unauthorized_user_cannot_view_assign_page(): void
    {
        Permission::findOrCreate(PermissionTypes::COLLECTIONS_ASSIGN, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('services.assign'))->assertForbidden();
    }

    public function test_authorized_user_can_assign_services_to_driver(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::COLLECTIONS_ASSIGN]);

        $serviceA = $this->createService();
        $serviceB = $this->createService();
        $driver = Driver::factory()->create();

        $response = $this->post(route('services.assign.store'), [
            'service_date' => now()->toDateString(),
            'driver_id' => $driver->id,
            'service_ids' => [$serviceA->id, $serviceB->id],
        ]);

        $response->assertRedirect(route('services.assign', ['date' => now()->toDateString()]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'id' => $serviceA->id,
            'driver_id' => $driver->id,
            'status' => Service::STATUS_SCHEDULED,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $serviceB->id,
            'driver_id' => $driver->id,
            'status' => Service::STATUS_SCHEDULED,
        ]);
    }

    public function test_assign_requires_at_least_one_service(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::COLLECTIONS_ASSIGN]);

        $driver = Driver::factory()->create();

        $response = $this->from(route('services.assign'))
            ->post(route('services.assign.store'), [
                'service_date' => now()->toDateString(),
                'driver_id' => $driver->id,
                'service_ids' => [],
            ]);

        $response->assertRedirect(route('services.assign'));
        $response->assertSessionHasErrors('service_ids');
    }
}
