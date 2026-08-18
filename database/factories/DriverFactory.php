<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'parentarl_surname' => fake()->lastName(),
            'maternal_surname' => fake()->lastName(),
            'phone' => fake()->numerify('##########'),
            'zone_id' => Zone::factory(),
            'user_id' => User::factory(),
        ];
    }
}
