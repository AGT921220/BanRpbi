<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'folio' => 'CTR-'.fake()->unique()->numerify('######'),
            'client_id' => Client::factory(),
            'name' => fake()->randomElement([
                'Contrato estándar RPBI',
                'Contrato anual de recolección',
                'Contrato especial',
            ]),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 year'),
            'status' => Contract::STATUS_ACTIVE,
            'collection_frequency' => fake()->randomElement(Contract::FREQUENCIES),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
