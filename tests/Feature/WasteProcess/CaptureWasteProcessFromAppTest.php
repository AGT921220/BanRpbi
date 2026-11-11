<?php

namespace Tests\Feature\WasteProcess;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\User;
use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use App\Models\WasteProcessType;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CaptureWasteProcessFromAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_app_lista_captura_y_cierra_un_tratamiento(): void
    {
        $user = $this->actingAsUserWithPermissions([
            PermissionTypes::PROCESS_CREATE_PRS,
        ]);

        WasteProcessType::query()->create([
            'id' => WasteProcessType::PROCESS_INCINERACION,
            'name' => 'Incineración',
        ]);

        $process = $this->seedProcess($user);

        $this->getJson('/api/procesos')
            ->assertOk()
            ->assertJsonPath('data.0.id', $process->id)
            ->assertJsonPath('data.0.type_name', 'Incineración');

        $this->getJson('/api/procesos/'.$process->id)
            ->assertOk()
            ->assertJsonPath('data.is_incineracion', true)
            ->assertJsonPath('data.can_finish', false)
            ->assertJsonPath('data.details.0.manifest_id', $process->details()->value('manifest_id'));

        $payload = [
            'date_start' => '2026-11-11',
            'hour_start_machine' => '08:30',
            'hour_machine' => '11:15',
            'comments' => 'Sin novedad',
            'temp_camara_primaria' => '850.5',
            'temp_camara_secundaria' => '1100',
            'oxigeno' => '8.2',
            'finish' => false,
        ];

        $this->putJson('/api/procesos/'.$process->id, $payload)
            ->assertOk()
            ->assertJsonPath('message', 'Tratamiento actualizado correctamente.')
            ->assertJsonPath('data.can_finish', true)
            ->assertJsonPath('data.params.0.comments', 'Sin novedad')
            ->assertJsonPath('data.params.0.temp_camara_primaria', 850.5);

        $process->refresh();
        $this->assertFalse((bool) $process->is_finished);
        $this->assertSame('08:30', Carbon::parse($process->hour_start_machine)->format('H:i'));
        $this->assertSame('Sin novedad', $process->comments);
        $this->assertEquals(850.5, (float) $process->temp_camara_primaria);

        $this->putJson('/api/procesos/'.$process->id, array_merge($payload, ['finish' => true]))
            ->assertOk()
            ->assertJsonPath('message', 'Tratamiento cerrado correctamente.')
            ->assertJsonPath('data.is_finished', 1);

        $process->refresh();
        $detail = $process->details()->first();

        $this->assertTrue((bool) $process->is_finished);
        $this->assertSame($user->id, (int) $process->finished_by_id);
        $this->assertSame(1, (int) $detail->is_finished);
        $this->assertEquals(8, (float) $detail->process_weight);

        $this->getJson('/api/procesos')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    private function seedProcess(User $user): WasteProcess
    {
        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = Contract::factory()->create();

        $service = Service::query()->create([
            'service_date' => now()->toDateString(),
            'zone_id' => $zone->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'status' => Service::STATUS_COLLECTED,
        ]);

        $serviceDetail = ServiceDetail::query()->create([
            'service_id' => $service->id,
            'weight' => '8.00',
        ]);

        $manifest = Manifest::query()->create([
            'service_id' => $service->id,
            'public_uuid' => (string) Str::uuid(),
        ]);

        $process = WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-11-11',
            'is_finished' => false,
        ]);

        WasteProcessDetail::query()->create([
            'waste_process_id' => $process->id,
            'manifest_id' => $manifest->id,
            'service_detail_id' => $serviceDetail->id,
            'total_weight' => '8.00',
            'process_weight' => '0',
            'is_finished' => false,
            'is_completed' => false,
        ]);

        return $process;
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
