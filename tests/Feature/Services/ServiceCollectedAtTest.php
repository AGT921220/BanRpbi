<?php

namespace Tests\Feature\Services;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Service;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ServiceCollectedAtTest extends TestCase
{
    use RefreshDatabase;

    public function test_marca_collected_at_al_pasar_a_recolectado(): void
    {
        Carbon::setTestNow('2026-11-12 15:40:00');

        $service = $this->service();

        $service->status = Service::STATUS_COLLECTED;
        $service->save();

        $service->refresh();

        $this->assertSame('2026-11-12 15:40:00', $service->collected_at?->format('Y-m-d H:i:s'));
    }

    public function test_no_cambia_collected_at_si_ya_estaba_recolectado(): void
    {
        $collectedAt = Carbon::parse('2026-11-01 08:15:00');
        $service = $this->service([
            'status' => Service::STATUS_COLLECTED,
            'collected_at' => $collectedAt,
        ]);

        Carbon::setTestNow('2026-11-12 15:40:00');

        $service->status = Service::STATUS_IN_PROCESS;
        $service->save();
        $service->status = Service::STATUS_COLLECTED;
        $service->save();

        $service->refresh();

        $this->assertSame('2026-11-01 08:15:00', $service->collected_at?->format('Y-m-d H:i:s'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function service(array $attributes = []): Service
    {
        return Service::query()->create(array_merge([
            'service_date' => '2026-11-12',
            'zone_id' => Zone::factory()->create()->id,
            'client_id' => Client::factory()->create()->id,
            'contract_id' => Contract::factory()->create()->id,
            'status' => Service::STATUS_PENDING,
        ], $attributes));
    }
}
