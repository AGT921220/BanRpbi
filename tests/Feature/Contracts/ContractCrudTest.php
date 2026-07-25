<?php

namespace Tests\Feature\Contracts;

use App\Features\Permissions\Constants\PermissionTypes;
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

        Contract::factory()->create(['name' => 'Contrato estándar']);

        $response = $this->get(route('contracts.index'));

        $response->assertOk();
        $response->assertSee('Contrato estándar');
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

        $response = $this->post(route('contracts.store'), [
            'name' => 'Contrato anual',
            'notes' => 'Catálogo base',
            'duration_months' => 12,
            'frequency' => 'monthly',
        ]);

        $response->assertRedirect(route('contracts.index'));
        $this->assertDatabaseHas('contracts', [
            'name' => 'Contrato anual',
            'duration_months' => 12,
            'frequency' => 'monthly',
        ]);
    }

    public function test_cannot_create_contract_without_name(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_CREATE]);

        $response = $this->from(route('contracts.create'))
            ->post(route('contracts.store'), [
                'name' => '',
                'duration_months' => 12,
                'frequency' => 'monthly',
            ]);

        $response->assertRedirect(route('contracts.create'));
        $response->assertSessionHasErrors('name');
    }

    public function test_authorized_user_can_update_contract(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_UPDATE]);

        $contract = Contract::factory()->create(['name' => 'Original']);

        $response = $this->put(route('contracts.update', $contract), [
            'name' => 'Actualizado',
            'notes' => null,
            'duration_months' => 24,
            'frequency' => 'weekly',
        ]);

        $response->assertRedirect(route('contracts.index'));
        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'name' => 'Actualizado',
            'duration_months' => 24,
            'frequency' => 'weekly',
        ]);
    }

    public function test_authorized_user_can_delete_contract(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_DELETE]);

        $contract = Contract::factory()->create();

        $response = $this->delete(route('contracts.destroy', $contract));

        $response->assertRedirect(route('contracts.index'));
        $this->assertSoftDeleted('contracts', ['id' => $contract->id]);
    }

    public function test_contracts_can_be_filtered_by_name(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::CONTRACTS_VIEW]);

        Contract::factory()->create(['name' => 'Contrato Alpha']);
        Contract::factory()->create(['name' => 'Contrato Beta']);

        $response = $this->get(route('contracts.index', ['search' => 'Alpha']));

        $response->assertOk();
        $response->assertSee('Contrato Alpha');
        $response->assertDontSee('Contrato Beta');
    }
}
