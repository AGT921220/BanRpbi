<?php

namespace Tests\Feature\Catalogs;

use App\Models\City;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StateCityCatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<string>
     */
    private const BAJA_CALIFORNIA_CITIES = [
        'Ensenada',
        'Mexicali',
        'Tecate',
        'Tijuana',
        'Playas de Rosarito',
        'San Quintín',
        'San Felipe',
    ];

    public function test_setup_seeds_baja_california_municipalities(): void
    {
        $state = State::query()->where('name', 'Baja California')->first();

        $this->assertNotNull($state);
        $this->assertDatabaseCount('states', 1);
        $this->assertDatabaseCount('cities', 7);

        foreach (self::BAJA_CALIFORNIA_CITIES as $cityName) {
            $this->assertDatabaseHas('cities', [
                'state_id' => $state->id,
                'name' => $cityName,
            ]);
        }
    }

    public function test_setup_is_idempotent_and_does_not_duplicate_catalog(): void
    {
        $state = State::query()->firstOrCreate([
            'name' => 'Baja California',
        ]);

        foreach (self::BAJA_CALIFORNIA_CITIES as $cityName) {
            City::query()->firstOrCreate([
                'state_id' => $state->id,
                'name' => $cityName,
            ]);
        }

        $this->assertDatabaseCount('states', 1);
        $this->assertDatabaseCount('cities', 7);
        $this->assertSame(1, State::query()->where('name', 'Baja California')->count());
        $this->assertSame(
            1,
            City::query()->where('state_id', $state->id)->where('name', 'Tijuana')->count(),
        );
    }
}
