<?php

namespace Tests\Feature\Clients;

use App\Features\Clients\Application\ListClients;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ListClientsTest extends TestCase
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

    public function test_calculates_records_total(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->count(3)->create();

        $result = app(ListClients::class)([]);

        $this->assertSame(3, $result['recordsTotal']);
        $this->assertSame(3, $result['recordsFiltered']);
        $this->assertCount(3, $result['data']);
    }

    public function test_records_filtered_ignores_limit_and_offset(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Anaís']);
        Client::factory()->create(['name' => 'Pedro']);

        $result = app(ListClients::class)([
            QueryFilter::whereAnyLike(['name'], 'Ana'),
            QueryOptions::orderBy('name', 'asc'),
            QueryOptions::offset(0),
            QueryOptions::limit(1),
        ]);

        $this->assertSame(3, $result['recordsTotal']);
        $this->assertSame(2, $result['recordsFiltered']);
        $this->assertCount(1, $result['data']);
    }

    public function test_applies_options_only_to_final_query(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create(['name' => 'Carlos', 'company' => 'Zeta']);
        Client::factory()->create(['name' => 'Beatriz', 'company' => 'Alpha']);
        Client::factory()->create(['name' => 'Ana', 'company' => 'Beta']);

        $result = app(ListClients::class)([
            QueryOptions::orderBy('name', 'asc'),
            QueryOptions::offset(1),
            QueryOptions::limit(1),
        ]);

        $this->assertSame(3, $result['recordsFiltered']);
        $this->assertCount(1, $result['data']);
        $this->assertSame('Beatriz', explode(' ', (string) $result['data'][0]['full_name'])[0]);
    }

    public function test_returns_datatables_json_shape(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_UPDATE,
            PermissionTypes::CLIENTS_DELETE,
        ]);

        $client = Client::factory()->create([
            'name' => 'Laura',
            'parentarl_surname' => 'Ruiz',
            'email' => 'laura@example.com',
            'phone' => '5511111111',
            'company' => 'Acme',
        ]);

        $result = app(ListClients::class)([], draw: 7);

        $this->assertSame(7, $result['draw']);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('recordsFiltered', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertSame($client->id, $result['data'][0]['id']);
        $this->assertSame('Laura Ruiz', $result['data'][0]['full_name']);
        $this->assertSame('laura@example.com', $result['data'][0]['email']);
        $this->assertSame('5511111111', $result['data'][0]['phone']);
        $this->assertSame('Acme', $result['data'][0]['company']);
        $this->assertNotEmpty($result['data'][0]['created_at']);
        $this->assertStringContainsString('Editar', $result['data'][0]['actions']);
        $this->assertStringContainsString('Eliminar', $result['data'][0]['actions']);
    }

    public function test_works_with_empty_modifiers_array(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->count(2)->create();

        $result = app(ListClients::class)([]);

        $this->assertSame(2, $result['recordsTotal']);
        $this->assertSame(2, $result['recordsFiltered']);
        $this->assertCount(2, $result['data']);
    }

    public function test_does_not_use_laravel_pagination(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->count(2)->create();

        $result = app(ListClients::class)([
            QueryOptions::limit(1),
        ]);

        $this->assertArrayNotHasKey('current_page', $result);
        $this->assertArrayNotHasKey('per_page', $result);
        $this->assertArrayNotHasKey('last_page', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
    }
}
