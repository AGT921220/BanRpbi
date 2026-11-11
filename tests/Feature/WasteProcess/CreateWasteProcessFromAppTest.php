<?php

namespace Tests\Feature\WasteProcess;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Manifest;
use App\Models\RpbiProfile;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\User;
use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use App\Models\WasteProcessType;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreateWasteProcessFromAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_agrupa_los_detalles_en_incineracion_y_esterilizacion(): void
    {
        Permission::findOrCreate(PermissionTypes::PROCESS_CREATE_PRS, 'web');
        Permission::findOrCreate(PermissionTypes::PROCESS_CREATE_PRAR, 'web');

        $user = $this->actingAsUserWithPermissions([
            PermissionTypes::SERVICES_REGISTER_ENTRY,
        ]);

        $pathological = RpbiProfile::query()->findOrFail(RpbiProfile::PATHOLOGICAL_ID);
        $punzocortante = RpbiProfile::query()->where('code', 'BI2')->firstOrFail();
        $sangre = RpbiProfile::query()->where('code', 'BI5')->firstOrFail();

        $this->assertNotSame(RpbiProfile::PATHOLOGICAL_ID, $punzocortante->id);
        $this->assertNotSame(RpbiProfile::PATHOLOGICAL_ID, $sangre->id);

        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = Contract::factory()->create();
        $service = Service::query()->create([
            'service_date' => now()->toDateString(),
            'zone_id' => $zone->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'status' => Service::STATUS_COLLECTED,
            'warehouse_entry_at' => now(),
        ]);

        $pathologicalDetail = $this->detail($service, $pathological, '4.50');
        $punzocortanteDetail = $this->detail($service, $punzocortante, '2.00');
        $sangreDetail = $this->detail($service, $sangre, '1.25');

        $manifest = Manifest::query()->create([
            'service_id' => $service->id,
            'public_uuid' => (string) Str::uuid(),
        ]);

        $response = $this->postJson('/api/procesos', [
            'date_start' => '2026-11-11',
            'manifest_ids' => [$manifest->id],
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data.ids')
            ->assertJsonMissingPath('data.id');

        $this->assertSame(2, WasteProcess::query()->count());
        $this->assertSame(3, WasteProcessDetail::query()->count());

        $incineracionId = $this->processIdForDetail($pathologicalDetail->id);
        $esterilizacionId = $this->processIdForDetail($punzocortanteDetail->id);

        $this->assertSame($esterilizacionId, $this->processIdForDetail($sangreDetail->id));
        $this->assertNotSame($incineracionId, $esterilizacionId);
        $this->assertSame(WasteProcessType::PROCESS_INCINERACION, $this->typeIdOfProcess($incineracionId));
        $this->assertSame(WasteProcessType::PROCESS_ESTERILIZACION, $this->typeIdOfProcess($esterilizacionId));
        $this->assertSame(1, WasteProcessDetail::query()->where('waste_process_id', $incineracionId)->count());
        $this->assertSame(2, WasteProcessDetail::query()->where('waste_process_id', $esterilizacionId)->count());
        $this->assertSame($user->id, (int) WasteProcess::query()->find($incineracionId)?->user_id);
        $this->assertSame($user->id, (int) WasteProcess::query()->find($esterilizacionId)?->user_id);

        $this->assertSame(Service::STATUS_IN_PROCESS, $service->fresh()->status);
    }

    private function processIdForDetail(int $serviceDetailId): int
    {
        return (int) WasteProcessDetail::query()
            ->where('service_detail_id', $serviceDetailId)
            ->value('waste_process_id');
    }

    private function typeIdOfProcess(int $processId): int
    {
        return (int) WasteProcess::query()->find($processId)?->waste_process_type_id;
    }

    private function detail(Service $service, RpbiProfile $profile, string $weight): ServiceDetail
    {
        return ServiceDetail::query()->create([
            'service_id' => $service->id,
            'weight' => $weight,
            'rpbi_profile_id' => $profile->id,
        ]);
    }

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
        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
