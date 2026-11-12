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
        $user = $this->actingAsLogbookUser();
        $this->processType(WasteProcessType::PROCESS_INCINERACION, 'Incineración');

        $inside = WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-11-02',
            'temp_camara_primaria' => 800,
        ]);

        $other = WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-11-03',
            'temp_camara_primaria' => 900,
        ]);

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-10-01',
        ]);

        $filtered = $this->get(route('procesos.bitacoras.tratamientos', [
            'filtro' => 'incineracion',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
        ]));

        $filtered->assertOk();
        $filtered->assertSee('Bitácora de tratamientos');
        $filtered->assertSee('Sólo incineración');
        $filtered->assertSee('02/11/2026');
        $filtered->assertDontSee('01/10/2026');
        $filtered->assertDontSee('Bitácora Incineración');
        $filtered->assertDontSee('Bitácora Esterilización');

        $generated = $this->getJson(route('procesos.bitacoras.tratamientos', [
            'filtro' => 'incineracion',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'generate' => 1,
            'process_ids' => [$inside->id],
        ]));

        $generated->assertOk();
        $generated->assertJsonPath('filtro', 'incineracion');
        $generated->assertJsonPath('incineracion.titulo', 'BITACORA DE TRATAMIENTO POR INCINERACION MENSUAL');
        $generated->assertJsonPath('incineracion.rows.0.fecha', '2/11/26');
        $generated->assertJsonPath('incineracion.rows.0.cl_en_rps', '0.00');
        $generated->assertJsonPath('incineracion.rows.0.temp_camara_primaria', '800.00');
        $generated->assertJsonMissingPath('esterilizacion');
        $generated->assertJsonMissingPath('incineracion.rows.1');
        $this->assertNotSame($inside->id, $other->id);
    }

    public function test_esterilizacion_pdf_suma_corrientes_y_respeta_el_filtro(): void
    {
        $user = $this->actingAsLogbookUser();
        $this->processType(WasteProcessType::PROCESS_INCINERACION, 'Incineración');
        $this->processType(WasteProcessType::PROCESS_ESTERILIZACION, 'Esterilización');

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-11-04',
            'temp_entrada_venturi' => 180,
        ]);

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_ESTERILIZACION,
            'date_start' => '2026-11-07',
            'hour_start_machine' => '08:30:00',
            'hour_machine' => '12:45:00',
            'eliminacion_biologica' => '99.90',
            'ciclo_carga' => '1',
            'rpbi_bi1_cepas' => 10.25,
            'rpbi_bi2_punzocortantes' => 1.5,
            'rpbi_bi4_no_anatomicos' => 2,
            'rpbi_bi5_sangre' => 3,
            'total_procesada' => 16.75,
        ]);

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_ESTERILIZACION,
            'date_start' => '2026-11-09',
            'rpbi_bi1_cepas' => 1.75,
            'total_procesada' => 1.75,
        ]);

        $page = $this->get(route('procesos.bitacoras.tratamientos', [
            'filtro' => 'esterilizacion',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
        ]));

        $page->assertOk();
        $page->assertSee('07/11/2026');
        $page->assertDontSee('04/11/2026');

        $generated = $this->getJson(route('procesos.bitacoras.tratamientos', [
            'filtro' => 'esterilizacion',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'generate' => 1,
        ]));

        $generated->assertOk();
        $generated->assertJsonPath('esterilizacion.titulo', 'REPORTE DE ESTERILIZACION MENSUAL (AUTOCLAVE)');
        $generated->assertJsonPath('esterilizacion.fila_total', 'Total kilos Por Corriente Esterilizados');
        $generated->assertJsonPath('esterilizacion.mes', 'NOVIEMBRE 2026');
        $generated->assertJsonPath('esterilizacion.operador', $user->name);
        $generated->assertJsonPath('esterilizacion.rows.0.fecha', '7');
        $generated->assertJsonPath('esterilizacion.rows.0.hora_carga', '08:30');
        $generated->assertJsonPath('esterilizacion.rows.0.hora_termino', '12:45');
        $generated->assertJsonPath('esterilizacion.rows.0.eliminacion_biologica', '99.90');
        $generated->assertJsonPath('esterilizacion.totales.bi1', '12.00');
        $generated->assertJsonPath('esterilizacion.totales.bi2', '1.50');
        $generated->assertJsonPath('esterilizacion.totales.total', '18.50');
        $generated->assertJsonMissingPath('incineracion');
    }

    public function test_ambas_incluye_las_dos_secciones(): void
    {
        $user = $this->actingAsLogbookUser();
        $this->processType(WasteProcessType::PROCESS_INCINERACION, 'Incineración');
        $this->processType(WasteProcessType::PROCESS_ESTERILIZACION, 'Esterilización');

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-11-02',
            'oxigeno' => 8,
        ]);

        WasteProcess::query()->create([
            'user_id' => $user->id,
            'waste_process_type_id' => WasteProcessType::PROCESS_ESTERILIZACION,
            'date_start' => '2026-11-03',
            'rpbi_bi5_sangre' => 4,
        ]);

        $generated = $this->getJson(route('procesos.bitacoras.tratamientos', [
            'filtro' => 'ambas',
            'generate' => 1,
        ]));

        $generated->assertOk();
        $generated->assertJsonPath('incineracion.rows.0.oxigeno', '8.00');
        $generated->assertJsonPath('esterilizacion.rows.0.rpbi_bi5_sangre', '4.00');
        $generated->assertJsonPath('esterilizacion.totales.bi5', '4.00');
    }

    public function test_las_rutas_anteriores_redirigen_a_la_bitacora_unificada(): void
    {
        $this->actingAsLogbookUser();

        $incineracion = $this->get('/procesos/bitacora/incineracion?date_from=2026-11-01');
        $incineracion->assertRedirect(route('procesos.bitacoras.tratamientos', [
            'date_from' => '2026-11-01',
            'filtro' => 'incineracion',
        ]));

        $esterilizacion = $this->get('/procesos/bitacora/esterilizacion');
        $esterilizacion->assertRedirect(route('procesos.bitacoras.tratamientos', [
            'filtro' => 'esterilizacion',
        ]));
    }

    public function test_logbook_requires_permission(): void
    {
        Permission::findOrCreate(PermissionTypes::LOGBOOKS_VIEW, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('procesos.bitacoras.tratamientos'))->assertForbidden();
    }

    private function actingAsLogbookUser(): User
    {
        Permission::findOrCreate(PermissionTypes::LOGBOOKS_VIEW, 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionTypes::LOGBOOKS_VIEW);
        $this->actingAs($user);

        return $user;
    }

    private function processType(int $id, string $name): void
    {
        if (WasteProcessType::query()->find($id) !== null) {
            return;
        }

        $type = new WasteProcessType;
        $type->id = $id;
        $type->name = $name;
        $type->save();
    }
}
