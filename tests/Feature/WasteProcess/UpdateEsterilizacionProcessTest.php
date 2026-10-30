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

class UpdateEsterilizacionProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_captura_de_esterilizacion_guarda_los_parametros_de_operacion(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::PROCESS_CREATE_PRAR]);

        $type = new WasteProcessType();
        $type->id = WasteProcessType::PROCESS_ESTERILIZACION;
        $type->name = 'Esterilización';
        $type->save();

        $process = WasteProcess::query()->create([
            'user_id' => auth()->id(),
            'waste_process_type_id' => WasteProcessType::PROCESS_ESTERILIZACION,
            'date_start' => '2026-10-29',
            'is_finished' => false,
        ]);

        $payload = [
            'date_start' => '2026-10-29',
            'hour_start_machine' => '07:15',
            'hour_machine' => '09:40',
            'comments' => 'Ciclo completo',
            'temp_esterilizador' => '134.5',
            'presion_camara_principal' => '2.1',
            'eliminacion_biologica' => 'Satisfactoria',
            'ciclo_carga' => 'C-03',
            'rpbi_bi1_cepas' => '12.5',
            'rpbi_bi2_punzocortantes' => '8',
            'rpbi_bi4_no_anatomicos' => '4.25',
            'rpbi_bi5_sangre' => '3',
            'total_procesada' => '27.75',
            'finish' => '0',
        ];

        $this->withSession(['_token' => 'test-token'])
            ->put(route('procesos.update', $process), $payload + ['_token' => 'test-token'])
            ->assertRedirect(route('procesos.edit', $process));

        $process->refresh();

        $this->assertSame('07:15', Carbon::parse($process->hour_start_machine)->format('H:i'));
        $this->assertSame('09:40', Carbon::parse($process->hour_machine)->format('H:i'));
        $this->assertSame('Ciclo completo', $process->comments);
        $this->assertEquals(134.5, (float) $process->temp_esterilizador);
        $this->assertEquals(2.1, (float) $process->presion_camara_principal);
        $this->assertSame('Satisfactoria', $process->eliminacion_biologica);
        $this->assertSame('C-03', $process->ciclo_carga);
        $this->assertEquals(12.5, (float) $process->rpbi_bi1_cepas);
        $this->assertEquals(8, (float) $process->rpbi_bi2_punzocortantes);
        $this->assertEquals(4.25, (float) $process->rpbi_bi4_no_anatomicos);
        $this->assertEquals(3, (float) $process->rpbi_bi5_sangre);
        $this->assertEquals(27.75, (float) $process->total_procesada);

        $this->get(route('procesos.edit', $process))
            ->assertOk()
            ->assertSee('Fecha de carga')
            ->assertSee('Hora de carga')
            ->assertSee('Hora término del proceso')
            ->assertSee('Temperatura del esterilizador')
            ->assertSee('Presión cámara principal')
            ->assertSee('Eliminación biológica')
            ->assertSee('Ciclo de carga')
            ->assertSee('RPBI BI1 Cepas y cultivos')
            ->assertSee('RPBI BI2 Punzocortantes')
            ->assertSee('RPBI BI4 No anatómicos')
            ->assertSee('RPBI BI5 Sangre')
            ->assertSee('Total procesada')
            ->assertSee('Observaciones')
            ->assertSee('Cantidades por manifiesto')
            ->assertDontSee('Peso a capturar')
            ->assertDontSee('Reactor')
            ->assertSee('Ciclo completo')
            ->assertSee('value="134.5"', false);

        $this->get(route('procesos.show', $process))
            ->assertOk()
            ->assertSee('Parámetros de esterilización')
            ->assertSee('134.5')
            ->assertSee('Satisfactoria');
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
