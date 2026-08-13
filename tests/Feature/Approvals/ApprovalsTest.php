<?php

namespace Tests\Feature\Approvals;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\Constants\RoleTypes;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Contract;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<string>  $permissions
     * @param  list<string>  $roles
     */
    private function actingAsUserWithPermissions(array $permissions, array $roles = []): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        if ($roles !== []) {
            $user->syncRoles($roles);
        }

        $this->actingAs($user);

        return $user;
    }

    private function pendingClient(): Client
    {
        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
            'zone_id' => Zone::factory()->create()->id,
            'configuration_submitted_at' => now(),
        ]);

        ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => Contract::factory()->create()->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
            'status' => ClientContract::STATUS_PENDING,
        ]);

        return $client->fresh() ?? $client;
    }

    public function test_authorized_user_can_view_approvals_index(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::APPROVALS_VIEW]);
        $this->pendingClient();

        $response = $this->get(route('approvals.index'));

        $response->assertOk();
        $response->assertViewIs('approvals.index');
        $response->assertSee('Clientes pendientes de aprobación');
        $response->assertSee('Director de Ventas');
        $response->assertSee('Director General');
    }

    public function test_single_director_approval_is_not_enough(): void
    {
        $this->actingAsUserWithPermissions(
            [PermissionTypes::APPROVALS_VIEW, PermissionTypes::CLIENT_CONTRACTS_APPROVE],
            [RoleTypes::DIRECTOR_VENTAS],
        );

        $client = $this->pendingClient();

        $response = $this->post(route('approvals.approve', $client));

        $response->assertRedirect(route('approvals.index'));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'client_id' => $client->id,
            'status' => ClientContract::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('client_configuration_approvals', [
            'client_id' => $client->id,
            'role_name' => RoleTypes::DIRECTOR_VENTAS,
        ]);
    }

    public function test_both_directors_activate_contract_and_replace_previous(): void
    {
        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
            'zone_id' => Zone::factory()->create()->id,
            'configuration_submitted_at' => now(),
        ]);

        $oldContract = ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => Contract::factory()->create(['name' => 'Viejo'])->id,
            'start_date' => '2025-08-01',
            'end_date' => '2026-08-01',
            'status' => ClientContract::STATUS_ACTIVE,
        ]);

        $newContract = ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => Contract::factory()->create(['name' => 'Nuevo'])->id,
            'start_date' => '2026-08-04',
            'end_date' => '2027-08-04',
            'status' => ClientContract::STATUS_PENDING,
        ]);

        $ventas = $this->actingAsUserWithPermissions(
            [PermissionTypes::APPROVALS_VIEW, PermissionTypes::CLIENT_CONTRACTS_APPROVE],
            [RoleTypes::DIRECTOR_VENTAS],
        );
        $this->post(route('approvals.approve', $client))->assertRedirect();

        $general = User::factory()->create();
        foreach ([PermissionTypes::APPROVALS_VIEW, PermissionTypes::CLIENT_CONTRACTS_APPROVE] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        Role::findOrCreate(RoleTypes::DIRECTOR_GENERAL, 'web');
        $general->givePermissionTo([
            PermissionTypes::APPROVALS_VIEW,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
        ]);
        $general->syncRoles([RoleTypes::DIRECTOR_GENERAL]);
        $this->actingAs($general);

        $this->post(route('approvals.approve', $client))->assertRedirect(route('approvals.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'configuration_status' => Client::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'id' => $oldContract->id,
            'status' => ClientContract::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'id' => $newContract->id,
            'status' => ClientContract::STATUS_ACTIVE,
        ]);
        $this->assertNotNull($ventas->id);
    }

    public function test_can_reject_client_with_reason_keeps_active_contract(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::APPROVALS_VIEW,
            PermissionTypes::APPROVALS_REJECT,
        ], [RoleTypes::DIRECTOR_GENERAL]);

        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
            'zone_id' => Zone::factory()->create()->id,
            'configuration_submitted_at' => now(),
        ]);

        $active = ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => Contract::factory()->create()->id,
            'start_date' => '2025-08-01',
            'end_date' => '2026-12-01',
            'status' => ClientContract::STATUS_ACTIVE,
        ]);

        ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => Contract::factory()->create()->id,
            'start_date' => '2026-08-04',
            'end_date' => '2027-08-04',
            'status' => ClientContract::STATUS_PENDING,
        ]);

        $response = $this->post(route('approvals.reject', $client), [
            'reason' => 'Faltan datos fiscales',
        ]);

        $response->assertRedirect(route('approvals.index'));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'configuration_status' => Client::STATUS_REJECTED,
            'configuration_rejection_reason' => 'Faltan datos fiscales',
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'id' => $active->id,
            'status' => ClientContract::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'client_id' => $client->id,
            'status' => ClientContract::STATUS_CANCELLED,
        ]);
    }

    public function test_unauthorized_user_cannot_approve(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::APPROVALS_VIEW]);
        $client = $this->pendingClient();

        $this->post(route('approvals.approve', $client))->assertForbidden();
    }

    public function test_user_without_director_role_cannot_complete_approval(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::APPROVALS_VIEW,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
        ]);

        $client = $this->pendingClient();

        $response = $this->post(route('approvals.approve', $client));

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
        ]);
    }
}
