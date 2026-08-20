<?php

namespace Tests\Feature\Drivers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverHeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_headers_returns_paginated_datatable_payload(): void
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create(['name' => 'chofer.portal']);
        $driver = Driver::factory()->create([
            'name' => 'Luis',
            'parentarl_surname' => 'García',
            'maternal_surname' => 'Pérez',
            'phone' => '5512345678',
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(route('driver-headers.index', [
            'offset' => 0,
            'limit' => 10,
            'draw' => 1,
        ]));

        $response->assertOk();
        $response->assertJsonPath('draw', 1);
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('recordsFiltered', 1);
        $response->assertJsonPath('data.0.id', $driver->id);
        $response->assertJsonPath('data.0.name', 'Luis García Pérez');
        $response->assertJsonPath('data.0.user', 'chofer.portal');
        $response->assertJsonPath('data.0.phone', '5512345678');
        $this->assertArrayNotHasKey('zone', $response->json('data.0'));
    }
}
