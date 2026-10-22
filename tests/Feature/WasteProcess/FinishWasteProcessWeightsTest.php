<?php

namespace Tests\Feature\WasteProcess;

use App\Features\WasteProcess\Helpers\ServiceDetailBalance;
use App\Features\WasteProcess\UseCases\FinishWasteProcess;
use App\Models\Client;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\User;
use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use App\Models\WasteProcessType;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinishWasteProcessWeightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_close_keeps_remaining_weight(): void
    {
        [$user, $process, $detail] = $this->seedProcess(available: 10.0);

        (new FinishWasteProcess(new ServiceDetailBalance()))(
            $process->id,
            collect([(object) ['id' => $detail->id, 'value' => 4]]),
            $user->id,
        );

        $detail->refresh();
        $balance = new ServiceDetailBalance();

        $this->assertSame(1, (int) $detail->is_finished);
        $this->assertSame(0, (int) $detail->is_completed);
        $this->assertSame(6.0, $balance->remainingWeight((int) $detail->service_detail_id));
        $this->assertTrue((bool) WasteProcess::query()->find($process->id)?->is_finished);
    }

    public function test_cannot_capture_more_than_remaining(): void
    {
        [$user, $process, $detail] = $this->seedProcess(available: 5.0);

        $this->expectException(ValidationException::class);

        (new FinishWasteProcess(new ServiceDetailBalance()))(
            $process->id,
            collect([(object) ['id' => $detail->id, 'value' => 6]]),
            $user->id,
        );
    }

    public function test_exhausts_line_when_treated_reaches_available(): void
    {
        [$user, $process, $detail] = $this->seedProcess(available: 8.0);

        (new FinishWasteProcess(new ServiceDetailBalance()))(
            $process->id,
            collect([(object) ['id' => $detail->id, 'value' => 8]]),
            $user->id,
        );

        $detail->refresh();
        $this->assertSame(1, (int) $detail->is_completed);
        $this->assertSame(0.0, (new ServiceDetailBalance())->remainingWeight((int) $detail->service_detail_id));
    }

    /**
     * @return array{0: User, 1: WasteProcess, 2: WasteProcessDetail}
     */
    private function seedProcess(float $available): array
    {
        WasteProcessType::query()->create([
            'id' => WasteProcessType::PROCESS_INCINERACION,
            'name' => 'Incineración',
        ]);

        $user = User::factory()->create();
        $zone = Zone::factory()->create();
        $client = Client::factory()->create(['zone_id' => $zone->id]);
        $contract = \App\Models\Contract::factory()->create();

        $service = Service::query()->create([
            'service_date' => now()->toDateString(),
            'zone_id' => $zone->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'status' => Service::STATUS_COLLECTED,
        ]);

        $serviceDetail = ServiceDetail::query()->create([
            'service_id' => $service->id,
            'weight' => (string) $available,
        ]);

        $manifest = Manifest::query()->create([
            'service_id' => $service->id,
            'status' => Manifest::STATUS_IN_PROCESS,
            'public_uuid' => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $process = WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => now()->toDateString(),
            'is_finished' => false,
        ]);

        $detail = WasteProcessDetail::query()->create([
            'waste_process_id' => $process->id,
            'manifest_id' => $manifest->id,
            'service_detail_id' => $serviceDetail->id,
            'total_weight' => number_format($available, 2, '.', ''),
            'process_weight' => '0',
            'is_finished' => false,
            'is_completed' => false,
        ]);

        return [$user, $process, $detail];
    }
}
