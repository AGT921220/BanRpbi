<?php

namespace Tests\Feature\Clients;

use App\Features\Clients\Application\ClientHeader;
use App\Features\Clients\Application\ClientHeaderSearchResult;
use App\Features\Clients\Application\SearchClientHeaders;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SearchClientHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_client_header_search_result(): void
    {
        Client::factory()->count(2)->create();

        $result = app(SearchClientHeaders::class)([]);

        $this->assertInstanceOf(ClientHeaderSearchResult::class, $result);
        $this->assertSame(2, $result->total);
        $this->assertSame(2, $result->filtered);
        $this->assertCount(2, $result->data);
        $this->assertContainsOnlyInstancesOf(ClientHeader::class, $result->data);
    }

    public function test_does_not_return_eloquent_models_as_final_contract(): void
    {
        Client::factory()->create();

        $result = app(SearchClientHeaders::class)([]);

        $this->assertInstanceOf(ClientHeader::class, $result->data->first());
        $this->assertNotInstanceOf(Client::class, $result->data->first());
    }

    public function test_search_result_does_not_contain_draw(): void
    {
        Client::factory()->create();

        $result = app(SearchClientHeaders::class)([]);
        $properties = array_keys(get_object_vars($result));

        $this->assertNotContains('draw', $properties);
        $this->assertNotContains('recordsTotal', $properties);
        $this->assertNotContains('recordsFiltered', $properties);
    }

    public function test_invoke_signature_does_not_accept_draw(): void
    {
        $method = new ReflectionMethod(SearchClientHeaders::class, '__invoke');
        $parameterNames = array_map(
            static fn ($parameter): string => $parameter->getName(),
            $method->getParameters(),
        );

        $this->assertSame(['modifiers', 'offset', 'limit'], $parameterNames);
        $this->assertNotContains('draw', $parameterNames);
    }

    public function test_calculates_total_and_filtered(): void
    {
        Client::factory()->count(3)->create();

        $result = app(SearchClientHeaders::class)([]);

        $this->assertSame(3, $result->total);
        $this->assertSame(3, $result->filtered);
        $this->assertCount(3, $result->data);
    }

    public function test_filtered_count_ignores_limit_and_offset(): void
    {
        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Anaís']);
        Client::factory()->create(['name' => 'Pedro']);

        $result = app(SearchClientHeaders::class)(
            modifiers: [
                QueryFilter::whereAnyLike(['name'], 'Ana'),
                QueryOptions::orderBy('name', 'asc'),
                QueryOptions::offset(0),
                QueryOptions::limit(1),
            ],
            offset: 0,
            limit: 1,
        );

        $this->assertSame(3, $result->total);
        $this->assertSame(2, $result->filtered);
        $this->assertCount(1, $result->data);
        $this->assertSame(1, $result->currentPage);
        $this->assertSame(1, $result->perPage);
    }

    public function test_applies_options_only_to_final_query(): void
    {
        Client::factory()->create(['name' => 'Carlos']);
        Client::factory()->create(['name' => 'Beatriz']);
        Client::factory()->create(['name' => 'Ana']);

        $result = app(SearchClientHeaders::class)(
            modifiers: [
                QueryOptions::orderBy('name', 'asc'),
                QueryOptions::offset(1),
                QueryOptions::limit(1),
            ],
            offset: 1,
            limit: 1,
        );

        $this->assertSame(3, $result->filtered);
        $this->assertCount(1, $result->data);
        $this->assertSame('Beatriz', explode(' ', $result->data->first()->fullName)[0]);
        $this->assertSame(2, $result->currentPage);
    }

    public function test_maps_projection_fields(): void
    {
        $client = Client::factory()->create([
            'name' => 'Laura',
            'parentarl_surname' => 'Ruiz',
            'email' => 'laura@example.com',
            'phone' => '5511111111',
            'company' => 'Acme',
        ]);

        $result = app(SearchClientHeaders::class)([]);
        $header = $result->data->first();

        $this->assertSame($client->id, $header->id);
        $this->assertSame('Laura Ruiz', $header->fullName);
        $this->assertSame('laura@example.com', $header->email);
        $this->assertSame('5511111111', $header->phone);
        $this->assertSame('Acme', $header->company);
        $this->assertFalse($header->hasContract);
        $this->assertSame([
            'id' => $client->id,
            'full_name' => 'Laura Ruiz',
            'email' => 'laura@example.com',
            'phone' => '5511111111',
            'company' => 'Acme',
            'created_at' => $header->createdAt,
            'has_contract' => false,
            'has_active_contract' => false,
            'has_collection_zone' => false,
            'configuration_status' => 'configuration_pending',
            'can_update' => false,
            'can_delete' => false,
            'can_configure' => false,
        ], $header->toArray());
    }

    public function test_has_contract_is_true_when_client_has_contracts(): void
    {
        $withContract = Client::factory()->create(['name' => 'ConContrato']);
        $withoutContract = Client::factory()->create(['name' => 'SinContrato']);

        ClientContract::query()->insert([
            'client_id' => $withContract->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(SearchClientHeaders::class)(
            modifiers: [
                QueryOptions::orderBy('name', 'asc'),
            ],
        );

        $byName = $result->data->keyBy(
            static fn (ClientHeader $header): string => explode(' ', $header->fullName)[0],
        );

        $this->assertTrue($byName['ConContrato']->hasContract);
        $this->assertFalse($byName['SinContrato']->hasContract);
    }

    public function test_permission_flags_reflect_authenticated_user(): void
    {
        Permission::findOrCreate(PermissionTypes::CLIENTS_UPDATE, 'web');
        Permission::findOrCreate(PermissionTypes::CLIENTS_DELETE, 'web');

        $user = User::factory()->create();
        $user->givePermissionTo([
            PermissionTypes::CLIENTS_UPDATE,
            PermissionTypes::CLIENTS_DELETE,
        ]);
        $this->actingAs($user);

        Client::factory()->create();

        $header = app(SearchClientHeaders::class)([])->data->first();

        $this->assertTrue($header->canUpdate);
        $this->assertTrue($header->canDelete);
    }

    public function test_does_not_use_laravel_paginate(): void
    {
        $source = file_get_contents(
            app_path('Features/Clients/Application/SearchClientHeaders.php'),
        );

        $this->assertIsString($source);
        $this->assertStringNotContainsString('paginate(', $source);
        $this->assertStringNotContainsString('simplePaginate(', $source);
        $this->assertStringNotContainsString('cursorPaginate(', $source);
    }

    public function test_works_with_empty_modifiers(): void
    {
        Client::factory()->count(2)->create();

        $result = app(SearchClientHeaders::class)([]);

        $this->assertSame(2, $result->total);
        $this->assertSame(2, $result->filtered);
        $this->assertCount(2, $result->data);
    }
}
