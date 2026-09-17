<?php

namespace Tests\Feature\Clients;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Contract;
use App\Models\RpbiProfile;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClientConfigurationTest extends TestCase
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

    private function contractWithProfiles(int $profileCount = 2, array $attributes = []): Contract
    {
        $contract = Contract::factory()->create($attributes);
        $profileIds = RpbiProfile::query()
            ->orderBy('code')
            ->limit($profileCount)
            ->pluck('id')
            ->all();

        $this->assertCount($profileCount, $profileIds);
        $contract->rpbiProfiles()->sync($profileIds);

        return $contract->fresh(['rpbiProfiles']) ?? $contract;
    }

    public function test_can_save_partial_configuration_without_submitting(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = $this->contractWithProfiles(2, ['duration_months' => 12]);

        $response = $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
            'notes' => 'Borrador',
        ]);

        $response->assertOk();
        $response->assertJsonPath('configuration_status', Client::STATUS_CONFIGURATION_PENDING);
        $response->assertJsonPath('has_contract', true);
        $response->assertJsonPath('has_collection_zone', true);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'zone_id' => $zone->id,
            'configuration_status' => Client::STATUS_CONFIGURATION_PENDING,
        ]);

        $this->assertDatabaseHas('client_contracts', [
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'status' => ClientContract::STATUS_PENDING,
            'price' => $contract->cost,
        ]);

        $this->assertDatabaseCount('contract_rpbi_profiles', 2);
        Mail::assertNothingSent();
    }

    public function test_submit_requires_contract(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
        ]);

        $client = Client::factory()->create([
            'zone_id' => Zone::factory()->create()->id,
        ]);

        $response = $this->postJson(route('clients.configuration.submit', $client));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contract_id']);
        Mail::assertNothingSent();
    }

    public function test_submit_requires_client_zone(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $client = Client::factory()->create(['zone_id' => null]);
        $contract = $this->contractWithProfiles(1);

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
        ])->assertOk();

        $response = $this->postJson(route('clients.configuration.submit', $client));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['zone_id']);
        Mail::assertNothingSent();
    }

    public function test_submit_takes_zone_from_client_without_asking_it(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = $this->contractWithProfiles(1);

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
        ])->assertOk();

        $response = $this->postJson(route('clients.configuration.submit', $client));

        $response->assertOk();
        $response->assertJsonPath('configuration_status', Client::STATUS_PENDING_APPROVAL);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'zone_id' => $zone->id,
        ]);
    }

    public function test_submit_requires_contract_with_profiles(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = Contract::factory()->create();

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
        ])->assertOk();

        $response = $this->postJson(route('clients.configuration.submit', $client));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contract_id']);
        Mail::assertNothingSent();
    }

    public function test_submit_sends_mail_and_changes_status(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = $this->contractWithProfiles(1);

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
        ])->assertOk();

        $response = $this->postJson(route('clients.configuration.submit', $client));

        $response->assertOk();
        $response->assertJsonPath('configuration_status', Client::STATUS_PENDING_APPROVAL);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
        ]);
    }

    public function test_show_configuration_returns_saved_draft(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = $this->contractWithProfiles(1);
        $profileId = $contract->rpbiProfiles->first()?->id;

        $this->assertNotNull($profileId);

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
            'notes' => 'Nota',
        ])->assertOk();

        $response = $this->getJson(route('clients.configuration.show', $client));

        $response->assertOk();
        $response->assertJsonPath('contract_id', $contract->id);
        $response->assertJsonPath('zone_id', $zone->id);
        $response->assertJsonPath('notes', 'Nota');
        $response->assertJsonPath('profile_ids.0', $profileId);
        $response->assertJsonPath('can_edit', true);
        $response->assertJsonPath('has_active_contract', false);
        $response->assertJsonPath('contract.cost', $contract->cost);
    }

    public function test_cannot_edit_when_pending_approval(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_PENDING_APPROVAL,
        ]);

        $response = $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => Contract::factory()->create()->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
        ]);

        $response->assertStatus(422);
    }

    public function test_show_configuration_is_readonly_while_active_contract_is_vigente(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_APPROVED,
            'zone_id' => Zone::factory()->create()->id,
        ]);

        ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => $this->contractWithProfiles(1)->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => ClientContract::STATUS_ACTIVE,
        ]);

        $response = $this->getJson(route('clients.configuration.show', $client));

        $response->assertOk();
        $response->assertJsonPath('can_edit', false);
        $response->assertJsonPath('has_active_contract', true);
    }

    public function test_cannot_save_replacement_while_active_contract_is_still_vigente(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_APPROVED,
            'zone_id' => $zone->id,
        ]);

        $active = ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => $this->contractWithProfiles(1, ['name' => 'Actual'])->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => ClientContract::STATUS_ACTIVE,
        ]);

        $replacementCatalog = $this->contractWithProfiles(1, ['name' => 'Reemplazo']);

        $response = $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $replacementCatalog->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contract_id']);
        $this->assertDatabaseHas('client_contracts', [
            'id' => $active->id,
            'status' => ClientContract::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseMissing('client_contracts', [
            'client_id' => $client->id,
            'contract_id' => $replacementCatalog->id,
        ]);
    }

    public function test_approved_client_can_save_replacement_when_active_contract_ended(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create([
            'configuration_status' => Client::STATUS_APPROVED,
            'zone_id' => $zone->id,
        ]);

        $active = ClientContract::query()->create([
            'client_id' => $client->id,
            'contract_id' => $this->contractWithProfiles(1, ['name' => 'Actual'])->id,
            'start_date' => now()->subYear()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => ClientContract::STATUS_ACTIVE,
        ]);

        $replacementCatalog = $this->contractWithProfiles(1, ['name' => 'Reemplazo']);

        $response = $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $replacementCatalog->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'configuration_status' => Client::STATUS_CONFIGURATION_PENDING,
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'id' => $active->id,
            'status' => ClientContract::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('client_contracts', [
            'client_id' => $client->id,
            'contract_id' => $replacementCatalog->id,
            'status' => ClientContract::STATUS_PENDING,
        ]);
    }

    public function test_saving_configuration_does_not_change_client_zone(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $zone = Zone::factory()->create();
        $otherZone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = $this->contractWithProfiles(1);

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'zone_id' => $otherZone->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
        ])->assertOk();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'zone_id' => $zone->id,
        ]);
    }
}
