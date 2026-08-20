<?php

namespace Tests\Feature\Drivers;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\Constants\RoleTypes;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createChoferUser(array $attributes = []): User
    {
        Role::findOrCreate(RoleTypes::CHOFER, 'web');

        $user = User::factory()->create($attributes);
        $user->assignRole(RoleTypes::CHOFER);

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
        $response->assertDontSee('>Zona</th>', false);
    }

    public function test_unauthorized_user_receives_forbidden(): void
    {
        Permission::findOrCreate(PermissionTypes::DRIVERS_VIEW, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('drivers.index'))->assertForbidden();
    }

    public function test_create_form_lists_available_chofer_users(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::DRIVERS_CREATE]);

        $availableChofer = $this->createChoferUser([
            'name' => 'Pedro Chofer',
            'email' => 'pedro.chofer@example.com',
        ]);
        $assignedChofer = $this->createChoferUser([
            'name' => 'Luis Asignado',
            'email' => 'luis.asignado@example.com',
        ]);
        $notChofer = User::factory()->create([
            'name' => 'Ana Ventas',
            'email' => 'ana.ventas@example.com',
        ]);

        Driver::factory()->create(['user_id' => $assignedChofer->id]);

        $response = $this->get(route('drivers.create'));

        $response->assertOk();
        $response->assertSee('Pedro Chofer');
        $response->assertSee('pedro.chofer@example.com');
        $response->assertDontSee('Luis Asignado');
        $response->assertDontSee('Ana Ventas');
        $response->assertSee('value="'.$availableChofer->id.'"', false);
        $response->assertDontSee('value="'.$notChofer->id.'"', false);
        $response->assertDontSee('driver-zone-id');
    }

    public function test_authorized_user_can_create_a_driver(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::DRIVERS_VIEW,
            PermissionTypes::DRIVERS_CREATE,
        ]);

        $chofer = $this->createChoferUser();

        $payload = [
            'name' => 'Carlos',
            'parentarl_surname' => 'López',
            'maternal_surname' => 'Martínez',
            'phone' => '5512345678',
            'user_id' => $chofer->id,
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
                'user_id' => '',
            ]);

        $response->assertRedirect(route('drivers.create'));
        $response->assertSessionHasErrors([
            'name',
            'parentarl_surname',
            'maternal_surname',
            'phone',
            'user_id',
        ]);
        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_cannot_create_a_driver_with_a_user_that_is_not_chofer(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::DRIVERS_CREATE]);

        $user = User::factory()->create();

        $response = $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [
                'name' => 'Carlos',
                'parentarl_surname' => 'López',
                'maternal_surname' => 'Martínez',
                'phone' => '5512345678',
                'user_id' => $user->id,
            ]);

        $response->assertRedirect(route('drivers.create'));
        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_cannot_assign_the_same_user_to_two_drivers(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::DRIVERS_CREATE]);

        $chofer = $this->createChoferUser();
        Driver::factory()->create(['user_id' => $chofer->id]);

        $response = $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [
                'name' => 'Carlos',
                'parentarl_surname' => 'López',
                'maternal_surname' => 'Martínez',
                'phone' => '5512345678',
                'user_id' => $chofer->id,
            ]);

        $response->assertRedirect(route('drivers.create'));
        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('drivers', 1);
    }

    public function test_authorized_user_can_update_a_driver(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::DRIVERS_VIEW,
            PermissionTypes::DRIVERS_UPDATE,
        ]);

        $driver = Driver::factory()->create([
            'name' => 'Original',
            'user_id' => $this->createChoferUser()->id,
        ]);
        $chofer = $this->createChoferUser();

        $payload = [
            'name' => 'Actualizado',
            'parentarl_surname' => 'Pérez',
            'maternal_surname' => 'Sánchez',
            'phone' => '5511111111',
            'user_id' => $chofer->id,
        ];

        $response = $this->put(route('drivers.update', $driver), $payload);

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success', 'Chofer actualizado correctamente.');
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            ...$payload,
        ]);
    }

    public function test_edit_form_includes_the_currently_assigned_user(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::DRIVERS_UPDATE]);

        $assignedChofer = $this->createChoferUser([
            'name' => 'Luis Asignado',
            'email' => 'luis.asignado@example.com',
        ]);
        $driver = Driver::factory()->create(['user_id' => $assignedChofer->id]);

        $response = $this->get(route('drivers.edit', $driver));

        $response->assertOk();
        $response->assertSee('Luis Asignado');
        $response->assertSee('luis.asignado@example.com');
        $response->assertSee('value="'.$assignedChofer->id.'"', false);
        $response->assertDontSee('driver-zone-id');
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
