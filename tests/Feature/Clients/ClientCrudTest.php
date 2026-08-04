<?php

namespace Tests\Feature\Clients;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClientCrudTest extends TestCase
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

    public function test_authorized_user_can_view_clients_index(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        $response = $this->get(route('clients.index'));

        $response->assertOk();
        $response->assertSee('Listado de clientes');
        $response->assertSee('clients-table');
        $response->assertSee(route('client-headers.index'), false);
    }

    public function test_clients_index_returns_view_even_with_datatable_params(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        $response = $this->get(route('clients.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));

        $response->assertOk();
        $response->assertViewIs('clients.index');
        $this->assertStringNotContainsString('"recordsTotal"', $response->getContent());
    }

    public function test_authorized_user_can_create_a_client(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_CREATE,
        ]);

        $payload = [
            'name' => 'Carlos',
            'parentarl_surname' => 'López',
            'email' => 'carlos.lopez@example.com',
            'phone' => '5512345678',
            'company' => 'Acme SA',
        ];

        $response = $this->post(route('clients.store'), $payload);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success', 'Cliente creado correctamente.');

        $this->assertDatabaseHas('clients', $payload);
    }

    public function test_client_email_must_be_unique(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_CREATE]);

        Client::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Otro',
                'parentarl_surname' => 'Cliente',
                'email' => 'duplicado@example.com',
                'phone' => '5598765432',
                'company' => 'Otra SA',
            ]);

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('clients', 1);
    }

    public function test_authorized_user_can_update_a_client(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_UPDATE,
        ]);

        $client = Client::factory()->create([
            'name' => 'Original',
            'email' => 'original@example.com',
        ]);

        $payload = [
            'name' => 'Actualizado',
            'parentarl_surname' => 'Pérez',
            'email' => 'actualizado@example.com',
            'phone' => '5511111111',
            'company' => 'Nueva Empresa',
        ];

        $response = $this->put(route('clients.update', $client), $payload);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success', 'Cliente actualizado correctamente.');
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            ...$payload,
        ]);
    }

    public function test_client_can_keep_own_email_when_updating(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_UPDATE]);

        $client = Client::factory()->create([
            'email' => 'mismo@example.com',
        ]);

        $response = $this->put(route('clients.update', $client), [
            'name' => 'Nombre',
            'parentarl_surname' => 'Apellido',
            'email' => 'mismo@example.com',
            'phone' => '5522222222',
            'company' => 'Empresa',
        ]);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'email' => 'mismo@example.com',
        ]);
    }

    public function test_authorized_user_can_delete_a_client(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_DELETE,
        ]);

        $client = Client::factory()->create();

        $response = $this->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success', 'Cliente eliminado correctamente.');
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_unauthorized_user_receives_forbidden(): void
    {
        Permission::findOrCreate(PermissionTypes::CLIENTS_VIEW, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('clients.index'));

        $response->assertForbidden();
    }

    public function test_invalid_data_returns_validation_errors(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_CREATE]);

        $response = $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => '',
                'parentarl_surname' => '',
                'email' => 'no-es-email',
                'phone' => '',
                'company' => '',
            ]);

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors([
            'name',
            'parentarl_surname',
            'email',
            'phone',
            'company',
        ]);
    }
}
