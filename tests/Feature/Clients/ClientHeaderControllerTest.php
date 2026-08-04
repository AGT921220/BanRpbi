<?php

namespace Tests\Feature\Clients;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Api\ClientHeaderController;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClientHeaderControllerTest extends TestCase
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

    public function test_returns_json_for_datatables(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_UPDATE,
            PermissionTypes::CLIENTS_DELETE,
        ]);

        Client::factory()->create([
            'name' => 'Ana',
            'parentarl_surname' => 'García',
        ]);

        $response = $this->getJson(route('client-headers.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonPath('draw', 1);
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('recordsFiltered', 1);
        $response->assertJsonPath('data.0.full_name', 'Ana García');
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('meta.filtered', 1);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                [
                    'id',
                    'full_name',
                    'email',
                    'phone',
                    'company',
                    'created_at',
                    'has_contract',
                    'has_collection_zone',
                    'configuration_status',
                    'can_update',
                    'can_delete',
                    'can_configure',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'filtered',
                'last_page',
            ],
        ]);
        $response->assertJsonPath('data.0.has_contract', false);
        $response->assertJsonPath('data.0.has_collection_zone', false);
        $response->assertJsonPath('data.0.configuration_status', 'configuration_pending');
        $response->assertJsonPath('data.0.can_update', true);
        $response->assertJsonPath('data.0.can_delete', true);
        $response->assertJsonPath('data.0.can_configure', false);
        $this->assertArrayNotHasKey('actions', $response->json('data.0'));
    }

    public function test_returns_mobile_payload_without_datatable_fields_with_data_flags(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create([
            'name' => 'Luis',
            'parentarl_surname' => 'Pérez',
        ]);

        $response = $this->getJson(route('client-headers.index', [
            'page' => 1,
            'per_page' => 20,
        ]));

        $response->assertOk();
        $response->assertJsonMissingPath('draw');
        $response->assertJsonMissingPath('recordsTotal');
        $response->assertJsonMissingPath('recordsFiltered');
        $response->assertJsonPath('data.0.full_name', 'Luis Pérez');
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('data.0.has_contract', false);
        $this->assertArrayNotHasKey('actions', $response->json('data.0'));
    }

    public function test_datatable_search_and_mobile_search_both_work(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create(['name' => 'Pedro']);
        Client::factory()->create(['name' => 'María']);

        $datatable = $this->getJson(route('client-headers.index', [
            'draw' => 1,
            'search' => ['value' => 'Pedro'],
        ]));

        $datatable->assertOk();
        $datatable->assertJsonPath('recordsFiltered', 1);
        $this->assertStringContainsString('Pedro', (string) $datatable->json('data.0.full_name'));

        $mobile = $this->getJson(route('client-headers.index', [
            'search' => 'María',
            'page' => 1,
            'per_page' => 10,
        ]));

        $mobile->assertOk();
        $mobile->assertJsonPath('meta.filtered', 1);
        $this->assertStringContainsString('María', (string) $mobile->json('data.0.full_name'));
    }

    public function test_datatable_pagination_is_normalized_from_start_and_length(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Beatriz']);
        Client::factory()->create(['name' => 'Carlos']);

        $response = $this->getJson(route('client-headers.index', [
            'draw' => 2,
            'start' => 1,
            'length' => 1,
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ],
        ]));

        $response->assertOk();
        $response->assertJsonPath('recordsFiltered', 3);
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 1);
        $response->assertJsonPath('data.0.full_name', fn (string $name): bool => str_starts_with($name, 'Beatriz'));
    }

    public function test_mobile_pagination_is_normalized_from_page_and_per_page(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Beatriz']);
        Client::factory()->create(['name' => 'Carlos']);

        $response = $this->getJson(route('client-headers.index', [
            'page' => 2,
            'per_page' => 1,
            'order_by' => 'name',
            'order_direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 1);
        $response->assertJsonPath('meta.filtered', 3);
        $response->assertJsonCount(1, 'data');
        $this->assertStringStartsWith('Beatriz', (string) $response->json('data.0.full_name'));
    }

    public function test_rejects_unsafe_mobile_order_by(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        Client::factory()->create(['name' => 'Zeta']);
        Client::factory()->create(['name' => 'Alpha']);

        $response = $this->getJson(route('client-headers.index', [
            'order_by' => 'password',
            'order_direction' => 'desc',
            'page' => 1,
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_does_not_apply_default_order_when_none_requested(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CLIENTS_VIEW]);

        $first = Client::factory()->create(['name' => 'Zeta']);
        $second = Client::factory()->create(['name' => 'Alpha']);

        $response = $this->getJson(route('client-headers.index', [
            'page' => 1,
            'per_page' => 10,
        ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertSame([$first->id, $second->id], $ids);
    }

    public function test_json_route_points_to_client_header_controller(): void
    {
        $route = Route::getRoutes()->getByName('client-headers.index');

        $this->assertNotNull($route);
        $this->assertSame(ClientHeaderController::class.'@index', $route->getActionName());
    }

    public function test_web_route_points_to_admin_client_controller(): void
    {
        $route = Route::getRoutes()->getByName('clients.index');

        $this->assertNotNull($route);
        $this->assertSame(ClientController::class.'@index', $route->getActionName());
    }

    public function test_admin_controller_does_not_inject_search_client_headers(): void
    {
        $parameters = (new ReflectionClass(ClientController::class))
            ->getConstructor()
            ?->getParameters() ?? [];

        $types = array_map(
            static fn ($parameter): ?string => $parameter->getType()?->getName(),
            $parameters,
        );

        $this->assertNotContains(
            \App\Features\Clients\Application\SearchClientHeaders::class,
            $types,
        );
    }

    public function test_client_data_table_controller_does_not_exist(): void
    {
        $this->assertFileDoesNotExist(
            app_path('Http/Controllers/Api/ClientDataTableController.php'),
        );
        $this->assertFileDoesNotExist(
            app_path('Http/Controllers/Admin/ClientDataTableController.php'),
        );
        $this->assertFalse(class_exists('App\\Http\\Controllers\\Api\\ClientDataTableController'));
    }
}
