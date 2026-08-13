<?php

namespace Tests\Feature\Drivers;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Driver;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DriverCrudTest extends TestCase
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

    public function test_authorized_user_can_view_drivers_index(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::DRIVERS_VIEW]);

        $response = $this->get(route('drivers.index'));

        $response->assertOk();
        $response->assertSee('Listado de choferes');
        $response->assertSee('drivers-table');
        $response->assertSee(route('driver-headers.index'), false);
    }

    public function test_unauthorized_user_receives_forbidden(): void
    {
        Permission::findOrCreate(PermissionTypes::DRIVERS_VIEW, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('drivers.index'))->assertForbidden();
    }

    public function test_authorized_user_can_create_a_driver(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::DRIVERS_VIEW,
            PermissionTypes::DRIVERS_CREATE,
        ]);

        $zone = Zone::factory()->create();

        $payload = [
            'name' => 'Carlos',
            'parentarl_surname' => 'López',
            'maternal_surname' => 'Martínez',
            'phone' => '5512345678',
            'zone_id' => $zone->id,
        ];

        $response = $this->post(route('drivers.store'), $payload);

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success', 'Chofer creado correctamente.');
        $this->assertDatabaseHas('drivers', $payload);
    }

    public function test_cannot_create_a_driver_without_required_fields(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::DRIVERS_CREATE]);

        $response = $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [
                'name' => '',
                'parentarl_surname' => '',
                'maternal_surname' => '',
                'phone' => '',
                'zone_id' => '',
            ]);

        $response->assertRedirect(route('drivers.create'));
        $response->assertSessionHasErrors([
            'name',
            'parentarl_surname',
            'maternal_surname',
            'phone',
            'zone_id',
        ]);
        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_authorized_user_can_update_a_driver(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::DRIVERS_VIEW,
            PermissionTypes::DRIVERS_UPDATE,
        ]);

        $driver = Driver::factory()->create([
            'name' => 'Original',
        ]);
        $zone = Zone::factory()->create();

        $payload = [
            'name' => 'Actualizado',
            'parentarl_surname' => 'Pérez',
            'maternal_surname' => 'Sánchez',
            'phone' => '5511111111',
            'zone_id' => $zone->id,
        ];

        $response = $this->put(route('drivers.update', $driver), $payload);

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success', 'Chofer actualizado correctamente.');
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            ...$payload,
        ]);
    }

    public function test_authorized_user_can_delete_a_driver(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::DRIVERS_VIEW,
            PermissionTypes::DRIVERS_DELETE,
        ]);

        $driver = Driver::factory()->create();

        $response = $this->delete(route('drivers.destroy', $driver));

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success', 'Chofer eliminado correctamente.');
        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }
}
