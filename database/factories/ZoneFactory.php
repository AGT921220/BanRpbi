<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lng = fake()->longitude(-100.5, -100.1);
        $lat = fake()->latitude(25.5, 25.8);

        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->hexColor(),
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [$lng, $lat],
                    [$lng + 0.02, $lat],
                    [$lng + 0.02, $lat + 0.02],
                    [$lng, $lat + 0.02],
                    [$lng, $lat],
                ]],
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
