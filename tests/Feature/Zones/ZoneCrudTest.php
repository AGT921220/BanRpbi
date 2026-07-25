<?php

namespace Tests\Feature\Zones;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ZoneCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{type: string, coordinates: list<list<list<float>>>}
     */
    private function validPolygon(): array
    {
        return [
            'type' => 'Polygon',
            'coordinates' => [[
                [-100.3161, 25.6866],
                [-100.2702, 25.6874],
                [-100.2759, 25.6502],
                [-100.3161, 25.6866],
            ]],
        ];
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

    public function test_authorized_user_can_view_zones_index(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_VIEW]);

        Zone::factory()->create(['name' => 'Zona Norte']);

        $response = $this->get(route('zones.index'));

        $response->assertOk();
        $response->assertSee('Zona Norte');
    }

    public function test_unauthorized_user_receives_forbidden(): void
    {
        Permission::findOrCreate(PermissionTypes::ZONES_VIEW, 'web');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('zones.index'))->assertForbidden();
    }

    public function test_authorized_user_can_create_zone_with_valid_geojson(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::ZONES_VIEW,
            PermissionTypes::ZONES_CREATE,
        ]);

        $payload = [
            'name' => 'Zona Centro',
            'description' => 'Área central',
            'color' => '#206bc4',
            'geometry' => $this->validPolygon(),
            'is_active' => true,
        ];

        $response = $this->post(route('zones.store'), $payload);

        $response->assertRedirect(route('zones.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('zones', [
            'name' => 'Zona Centro',
            'color' => '#206bc4',
        ]);
    }

    public function test_cannot_create_zone_without_name(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_CREATE]);

        $response = $this->from(route('zones.create'))
            ->post(route('zones.store'), [
                'name' => '',
                'geometry' => $this->validPolygon(),
            ]);

        $response->assertRedirect(route('zones.create'));
        $response->assertSessionHasErrors('name');
    }

    public function test_cannot_create_zone_without_geometry(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_CREATE]);

        $response = $this->from(route('zones.create'))
            ->post(route('zones.store'), [
                'name' => 'Sin geometría',
            ]);

        $response->assertRedirect(route('zones.create'));
        $response->assertSessionHasErrors('geometry');
    }

    public function test_cannot_create_zone_with_fewer_than_three_vertices(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_CREATE]);

        $response = $this->from(route('zones.create'))
            ->post(route('zones.store'), [
                'name' => 'Polígono inválido',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-100.3, 25.6],
                        [-100.2, 25.6],
                        [-100.3, 25.6],
                    ]],
                ],
            ]);

        $response->assertRedirect(route('zones.create'));
        $response->assertSessionHasErrors('geometry');
    }

    public function test_cannot_create_zone_with_invalid_coordinates(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_CREATE]);

        $response = $this->from(route('zones.create'))
            ->post(route('zones.store'), [
                'name' => 'Coordenadas inválidas',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-200.0, 25.6],
                        [-100.2, 25.6],
                        [-100.2, 25.7],
                        [-200.0, 25.6],
                    ]],
                ],
            ]);

        $response->assertRedirect(route('zones.create'));
        $response->assertSessionHasErrors('geometry');
    }

    public function test_authorized_user_can_update_zone(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::ZONES_VIEW,
            PermissionTypes::ZONES_UPDATE,
        ]);

        $zone = Zone::factory()->create(['name' => 'Original']);

        $response = $this->put(route('zones.update', $zone), [
            'name' => 'Actualizada',
            'description' => 'Descripción nueva',
            'color' => '#ff0000',
            'geometry' => $this->validPolygon(),
            'is_active' => true,
        ]);

        $response->assertRedirect(route('zones.index'));
        $this->assertDatabaseHas('zones', [
            'id' => $zone->id,
            'name' => 'Actualizada',
            'color' => '#ff0000',
        ]);
    }

    public function test_authorized_user_can_toggle_zone_status(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_UPDATE]);

        $zone = Zone::factory()->create(['is_active' => true]);

        $response = $this->patch(route('zones.toggle-status', $zone));

        $response->assertRedirect(route('zones.index'));
        $this->assertDatabaseHas('zones', [
            'id' => $zone->id,
            'is_active' => false,
        ]);
    }

    public function test_authorized_user_can_delete_zone_without_dependencies(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::ZONES_VIEW,
            PermissionTypes::ZONES_DELETE,
        ]);

        $zone = Zone::factory()->create();

        $response = $this->delete(route('zones.destroy', $zone));

        $response->assertRedirect(route('zones.index'));
        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
    }

    public function test_zones_index_can_be_filtered_by_name(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_VIEW]);

        Zone::factory()->create(['name' => 'Zona Alpha']);
        Zone::factory()->create(['name' => 'Zona Beta']);

        $response = $this->get(route('zones.index', ['search' => 'Alpha']));

        $response->assertOk();
        $response->assertSee('Zona Alpha');
        $response->assertDontSee('Zona Beta');
    }

    public function test_edit_view_returns_geometry(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::ZONES_UPDATE]);

        $geometry = $this->validPolygon();
        $zone = Zone::factory()->create([
            'name' => 'Con geometría',
            'geometry' => $geometry,
        ]);

        $response = $this->get(route('zones.edit', $zone));

        $response->assertOk();
        $response->assertSee('Con geometría', false);
        $response->assertSee('current-zone-geometry', false);
        $response->assertSee('-100.3161', false);
    }
}
