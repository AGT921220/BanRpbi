<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'parentarl_surname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'company' => fake()->company(),
            'nra' => fake()->bothify('NRA-########'),
            'rfc' => strtoupper(fake()->lexify('????').fake()->numerify('######').fake()->bothify('???')),
            'street' => fake()->streetName(),
            'num_ext' => (string) fake()->numberBetween(1, 9999),
            'num_int' => fake()->optional()->bothify('??-##'),
            'postal_code' => fake()->numerify('#####'),
            'colony' => fake()->optional()->streetName(),
            'state_id' => null,
            'city_id' => null,
            'maps_url' => null,
            'maps_place_id' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }
}
