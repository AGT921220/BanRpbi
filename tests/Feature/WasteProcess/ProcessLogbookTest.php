<?php

namespace Tests\Feature\WasteProcess;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\User;
use App\Models\WasteProcess;
use App\Models\WasteProcessType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProcessLogbookTest extends TestCase
{
    use RefreshDatabase;

    public function test_logbook_filters_by_date_range_and_selection(): void
    {
        foreach ([PermissionTypes::LOGBOOKS_VIEW] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo(PermissionTypes::LOGBOOKS_VIEW);
        $this->actingAs($user);

        $type = new WasteProcessType;
        $type->id = WasteProcessType::PROCESS_INCINERACION;
        $type->name = 'Incineración';
        $type->save();

        $inside = WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-11-02',
        ]);

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-10-01',
        ]);

        $filtered = $this->get(route('procesos.bitacora', [
            'type' => 'incineracion',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
        ]));

        $filtered->assertOk();
        $filtered->assertSee('Bitácora de incineración');
        $filtered->assertSee('02/11/2026');
        $filtered->assertDontSee('01/10/2026');

        $generated = $this->get(route('procesos.bitacora', [
            'type' => 'incineracion',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'generate' => 1,
            'process_ids' => [$inside->id],
        ]));

        $generated->assertOk();
        $generated->assertSee('Tratamiento #'.$inside->id);
    }

    public function test_logbook_requires_permission(): void
    {
        Permission::findOrCreate(PermissionTypes::LOGBOOKS_VIEW, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('procesos.bitacora', 'incineracion'))->assertForbidden();
    }
}
