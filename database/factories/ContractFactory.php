<?php

namespace Database\Factories;

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
        return [
            'name' => fake()->unique()->randomElement([
                'Contrato estándar RPBI',
                'Contrato anual de recolección',
                'Contrato especial industrial',
                'Contrato básico',
            ]).' '.fake()->unique()->numerify('##'),
            'notes' => fake()->optional()->sentence(),
            'duration_months' => 12,
            'frequency' => fake()->randomElement(Contract::FREQUENCIES),
        ];
    }
}
