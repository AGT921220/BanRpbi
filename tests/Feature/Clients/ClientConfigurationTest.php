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

        $client = Client::factory()->create();
        $contract = $this->contractWithProfiles(2, ['duration_months' => 12]);
        $zone = Zone::factory()->create();

        $response = $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'zone_id' => $zone->id,
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

    public function test_submit_requires_contract_and_zone(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
        ]);

        $client = Client::factory()->create();

        $response = $this->postJson(route('clients.configuration.submit', $client));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contract_id']);
        Mail::assertNothingSent();
    }

    public function test_submit_requires_contract_with_profiles(): void
    {
        Mail::fake();

        $this->actingAsUserWithPermissions([
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
        ]);

        $client = Client::factory()->create();
        $contract = Contract::factory()->create();
        $zone = Zone::factory()->create();

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'zone_id' => $zone->id,
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

        $client = Client::factory()->create();
        $contract = $this->contractWithProfiles(1);
        $zone = Zone::factory()->create();

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'zone_id' => $zone->id,
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

        $client = Client::factory()->create();
        $contract = $this->contractWithProfiles(1);
        $zone = Zone::factory()->create();
        $profileId = $contract->rpbiProfiles->first()?->id;

        $this->assertNotNull($profileId);

        $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $contract->id,
            'zone_id' => $zone->id,
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
            'zone_id' => Zone::factory()->create()->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_approved_client_can_save_replacement_without_touching_active(): void
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
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => ClientContract::STATUS_ACTIVE,
        ]);

        $replacementCatalog = $this->contractWithProfiles(1, ['name' => 'Reemplazo']);

        $response = $this->putJson(route('clients.configuration.save', $client), [
            'contract_id' => $replacementCatalog->id,
            'zone_id' => $zone->id,
            'start_date' => '2026-08-04',
            'end_date' => '2027-08-04',
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
}
