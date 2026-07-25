<?php

namespace Tests\Feature\Contracts;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ContractCrudTest extends TestCase
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

    public function test_authorized_user_can_view_contracts_index(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_VIEW]);

        $contract = Contract::factory()->create(['folio' => 'CTR-100']);

        $response = $this->get(route('contracts.index'));

        $response->assertOk();
        $response->assertSee('CTR-100');
        $response->assertSee($contract->name);
    }

    public function test_unauthorized_user_receives_forbidden(): void
    {
        Permission::findOrCreate(PermissionTypes::CONTRACTS_VIEW, 'web');
        $this->actingAs(User::factory()->create());

        $this->get(route('contracts.index'))->assertForbidden();
    }

    public function test_authorized_user_can_create_contract(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CONTRACTS_VIEW,
            PermissionTypes::CONTRACTS_CREATE,
        ]);

        $client = Client::factory()->create();

        $response = $this->post(route('contracts.store'), [
            'folio' => 'CTR-200',
            'client_id' => $client->id,
            'name' => 'Contrato anual',
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-12-31',
            'status' => Contract::STATUS_ACTIVE,
            'collection_frequency' => 'monthly',
            'notes' => 'Notas de prueba',
        ]);

        $response->assertRedirect(route('contracts.index'));
        $this->assertDatabaseHas('contracts', [
            'folio' => 'CTR-200',
            'client_id' => $client->id,
            'name' => 'Contrato anual',
            'status' => Contract::STATUS_ACTIVE,
        ]);
    }

    public function test_cannot_create_contract_with_invalid_dates(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_CREATE]);

        $client = Client::factory()->create();

        $response = $this->from(route('contracts.create'))
            ->post(route('contracts.store'), [
                'folio' => 'CTR-300',
                'client_id' => $client->id,
                'name' => 'Contrato inválido',
                'starts_at' => '2026-12-31',
                'ends_at' => '2026-01-01',
                'status' => Contract::STATUS_DRAFT,
            ]);

        $response->assertRedirect(route('contracts.create'));
        $response->assertSessionHasErrors('ends_at');
    }

    public function test_authorized_user_can_update_contract(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_UPDATE]);

        $contract = Contract::factory()->create(['name' => 'Original']);

        $response = $this->put(route('contracts.update', $contract), [
            'folio' => $contract->folio,
            'client_id' => $contract->client_id,
            'name' => 'Actualizado',
            'starts_at' => $contract->starts_at->format('Y-m-d'),
            'ends_at' => $contract->ends_at->format('Y-m-d'),
            'status' => Contract::STATUS_ACTIVE,
            'collection_frequency' => 'weekly',
            'notes' => null,
        ]);

        $response->assertRedirect(route('contracts.index'));
        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'name' => 'Actualizado',
            'collection_frequency' => 'weekly',
        ]);
    }

    public function test_authorized_user_can_delete_contract(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_DELETE]);

        $contract = Contract::factory()->create();

        $response = $this->delete(route('contracts.destroy', $contract));

        $response->assertRedirect(route('contracts.index'));
        $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
    }

    public function test_contracts_can_be_filtered_by_folio(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_VIEW]);

        Contract::factory()->create(['folio' => 'CTR-AAA']);
        Contract::factory()->create(['folio' => 'CTR-BBB']);

        $response = $this->get(route('contracts.index', ['search' => 'CTR-AAA']));

        $response->assertOk();
        $response->assertSee('CTR-AAA');
        $response->assertDontSee('CTR-BBB');
    }
}
