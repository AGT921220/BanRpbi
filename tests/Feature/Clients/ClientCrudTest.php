<?php

namespace Tests\Feature\Clients;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\City;
use App\Models\Client;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
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

        $zone = Zone::factory()->create(['name' => 'Zona Norte']);

        $payload = [
            'name' => 'Carlos',
            'parentarl_surname' => 'López',
            'email' => 'carlos.lopez@example.com',
            'phone' => '5512345678',
            'company' => 'Acme SA',
            'nra' => 'NRA-12345678',
            'rfc' => 'LOPC800101ABC',
            'zone_id' => $zone->id,
            'street' => 'Av. Reforma',
            'num_ext' => '123',
            'num_int' => '4B',
            'postal_code' => '06600',
            'colony' => 'Juárez',
            'city' => 'Tijuana',
            'state' => 'Baja California',
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=19.432608,-99.133209',
            'maps_place_id' => 'ChIJB3Uh6w0AzYURQPE0TGlQONQ',
            'latitude' => '19.4326080',
            'longitude' => '-99.1332090',
        ];

        $response = $this->post(route('clients.store'), $payload);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success', 'Cliente creado correctamente.');

        $state = State::query()->where('name', 'Baja California')->firstOrFail();
        $city = City::query()
            ->where('state_id', $state->id)
            ->where('name', 'Tijuana')
            ->firstOrFail();

        unset($payload['city'], $payload['state']);

        $this->assertDatabaseHas('clients', [
            ...$payload,
            'state_id' => $state->id,
            'city_id' => $city->id,
        ]);
    }

    public function test_client_email_must_be_unique(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_CREATE]);

        Client::factory()->create(['email' => 'duplicado@example.com']);
        $zone = Zone::factory()->create();

        $response = $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Otro',
                'parentarl_surname' => 'Cliente',
                'email' => 'duplicado@example.com',
                'phone' => '5598765432',
                'company' => 'Otra SA',
                'nra' => 'NRA-87654321',
                'rfc' => 'OCLC900202XYZ',
                'zone_id' => $zone->id,
                'street' => 'Calle Norte',
                'num_ext' => '10',
                'postal_code' => '64000',
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
        $zone = Zone::factory()->create(['name' => 'Zona Sur']);

        $payload = [
            'name' => 'Actualizado',
            'parentarl_surname' => 'Pérez',
            'email' => 'actualizado@example.com',
            'phone' => '5511111111',
            'company' => 'Nueva Empresa',
            'nra' => 'NRA-11223344',
            'rfc' => 'PEXA850101AB1',
            'zone_id' => $zone->id,
            'street' => 'Calle Sur',
            'num_ext' => '55',
            'num_int' => null,
            'postal_code' => '03100',
            'colony' => 'Del Valle',
            'city' => 'Mexicali',
            'state' => 'Baja California',
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=19.390000,-99.140000',
            'maps_place_id' => 'ChIJb8AwRWkBzoUR4nR2x6WZq7E',
            'latitude' => '19.3900000',
            'longitude' => '-99.1400000',
        ];

        $response = $this->put(route('clients.update', $client), $payload);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success', 'Cliente actualizado correctamente.');

        $state = State::query()->where('name', 'Baja California')->firstOrFail();
        $city = City::query()
            ->where('state_id', $state->id)
            ->where('name', 'Mexicali')
            ->firstOrFail();

        unset($payload['city'], $payload['state']);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            ...$payload,
            'state_id' => $state->id,
            'city_id' => $city->id,
        ]);
    }

    public function test_client_city_must_belong_to_catalog_state(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_CREATE]);

        $zone = Zone::factory()->create();

        $response = $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Carlos',
                'parentarl_surname' => 'López',
                'email' => 'fuera.catalogo@example.com',
                'phone' => '5512345678',
                'company' => 'Acme SA',
                'nra' => 'NRA-12345678',
                'rfc' => 'LOPC800101ABC',
                'zone_id' => $zone->id,
                'street' => 'Av. Reforma',
                'postal_code' => '06600',
                'city' => 'Guadalajara',
                'state' => 'Baja California',
            ]);

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors('city');
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_client_can_keep_own_email_when_updating(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_UPDATE]);

        $client = Client::factory()->create([
            'email' => 'mismo@example.com',
        ]);
        $zone = Zone::factory()->create();

        $response = $this->put(route('clients.update', $client), [
            'name' => 'Nombre',
            'parentarl_surname' => 'Apellido',
            'email' => 'mismo@example.com',
            'phone' => '5522222222',
            'company' => 'Empresa',
            'nra' => 'NRA-55667788',
            'rfc' => 'NOMA900101XXX',
            'zone_id' => $zone->id,
            'street' => 'Calle Centro',
            'num_ext' => '1',
            'postal_code' => '01000',
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
                'nra' => '',
                'rfc' => 'BAD',
                'street' => '',
                'postal_code' => '',
            ]);

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors([
            'name',
            'parentarl_surname',
            'email',
            'phone',
            'company',
            'nra',
            'rfc',
            'zone_id',
            'street',
            'postal_code',
        ]);
    }

    public function test_create_form_shows_active_zones(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_CREATE]);

        Zone::factory()->create(['name' => 'Zona Activa']);
        Zone::factory()->inactive()->create(['name' => 'Zona Inactiva']);

        $response = $this->get(route('clients.create'));

        $response->assertOk();
        $response->assertSee('client-zone-id', false);
        $response->assertSee('Zona Activa');
        $response->assertDontSee('Zona Inactiva');
    }

    public function test_configure_modal_does_not_include_zone_step(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $response = $this->get(route('clients.index'));

        $response->assertOk();
        $response->assertSee('configure-contract-id', false);
        $response->assertSee('configure-client-zone', false);
        $response->assertSee('Se toma del alta del cliente');
        $response->assertSee('>Contrato</li>', false);
        $response->assertSee('>Resumen</li>', false);
        $response->assertSee('Zona del cliente');
        $response->assertDontSee('configure-zone-id', false);
        $response->assertDontSee('name="zone_id"', false);
        $response->assertDontSee('>Zona</li>', false);
    }

    public function test_cannot_create_client_without_zone(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_CREATE]);

        $response = $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Carlos',
                'parentarl_surname' => 'López',
                'email' => 'sin.zona@example.com',
                'phone' => '5512345678',
                'company' => 'Acme SA',
                'nra' => 'NRA-12345678',
                'rfc' => 'LOPC800101ABC',
                'street' => 'Av. Reforma',
                'postal_code' => '06600',
            ]);

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors('zone_id');
        $this->assertDatabaseCount('clients', 0);
    }
}
