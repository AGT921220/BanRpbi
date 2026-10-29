<?php

namespace Tests\Feature\WasteProcess;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\User;
use App\Models\WasteProcess;
use App\Models\WasteProcessType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UpdateIncineracionProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_captura_de_incineracion_guarda_los_parametros_de_operacion(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::PROCESS_CREATE_PRS]);

        WasteProcessType::query()->create([
            'id' => WasteProcessType::PROCESS_INCINERACION,
            'name' => 'Incineración',
        ]);

        $process = WasteProcess::query()->create([
            'user_id' => auth()->id(),
            'waste_process_type_id' => WasteProcessType::PROCESS_INCINERACION,
            'date_start' => '2026-10-29',
            'is_finished' => false,
        ]);

        $payload = [
            'date_start' => '2026-10-29',
            'hour_start_machine' => '08:30',
            'hour_machine' => '11:15',
            'comments' => 'Sin novedad',
            'temp_camara_primaria' => '850.5',
            'temp_camara_secundaria' => '1100',
            'temp_entrada_venturi' => '180.25',
            'temp_salida_venturi' => '90',
            'temp_salida_chimenea' => '70.5',
            'oxigeno' => '8.2',
            'presion_negativa' => '-1.5',
            'consumo_gas_lp' => '42.3',
            'consumo_promedio_agua' => '15',
            'evaporacion_agua_tratada' => '3.4',
            'patologicos' => '12.5',
            'cultivos_cepas' => '2',
            'sangre' => '4.75',
            'ceniza_generada' => '1.2',
            'descarga_lavador' => '6',
            'finish' => '0',
        ];

        $this->withSession(['_token' => 'test-token'])
            ->put(route('procesos.update', $process), $payload + ['_token' => 'test-token'])
            ->assertRedirect(route('procesos.edit', $process));

        $process->refresh();

        $this->assertSame('08:30', Carbon::parse($process->hour_start_machine)->format('H:i'));
        $this->assertSame('11:15', Carbon::parse($process->hour_machine)->format('H:i'));
        $this->assertSame('Sin novedad', $process->comments);
        $this->assertEquals(850.5, (float) $process->temp_camara_primaria);
        $this->assertEquals(1100, (float) $process->temp_camara_secundaria);
        $this->assertEquals(180.25, (float) $process->temp_entrada_venturi);
        $this->assertEquals(90, (float) $process->temp_salida_venturi);
        $this->assertEquals(70.5, (float) $process->temp_salida_chimenea);
        $this->assertEquals(8.2, (float) $process->oxigeno);
        $this->assertEquals(-1.5, (float) $process->presion_negativa);
        $this->assertEquals(42.3, (float) $process->consumo_gas_lp);
        $this->assertEquals(15, (float) $process->consumo_promedio_agua);
        $this->assertEquals(3.4, (float) $process->evaporacion_agua_tratada);
        $this->assertEquals(12.5, (float) $process->patologicos);
        $this->assertEquals(2, (float) $process->cultivos_cepas);
        $this->assertEquals(4.75, (float) $process->sangre);
        $this->assertEquals(1.2, (float) $process->ceniza_generada);
        $this->assertEquals(6, (float) $process->descarga_lavador);

        $this->get(route('procesos.edit', $process))
            ->assertOk()
            ->assertSee('Hora de carga')
            ->assertSee('Paro de carga')
            ->assertSee('Temp. cámara primaria')
            ->assertSee('Temp. cámara secundaria')
            ->assertSee('Temperatura entrada Venturi')
            ->assertSee('Temperatura salida Venturi')
            ->assertSee('Temperatura salida chimenea')
            ->assertSee('O2 (Oxígeno)')
            ->assertSee('Presión negativa (IN H2O)')
            ->assertSee('Consumo de gas LP')
            ->assertSee('Consumo promedio de agua')
            ->assertSee('Evaporación agua tratada')
            ->assertSee('Patológicos')
            ->assertSee('Cultivos y cepas')
            ->assertSee('Sangre')
            ->assertSee('Ceniza generada')
            ->assertSee('Descarga de lavador')
            ->assertSee('Observaciones')
            ->assertSee('Cantidades por manifiesto')
            ->assertDontSee('Peso a capturar')
            ->assertSee('Sin novedad')
            ->assertSee('value="850.5"', false);

        $this->get(route('procesos.show', $process))
            ->assertOk()
            ->assertSee('Parámetros de incineración')
            ->assertSee('850.5');
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
        $this->actingAs($user);

        return $user;
    }
}
