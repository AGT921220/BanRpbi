<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Illuminate\Console\Command;

class CreateDemoDataCommand extends Command
{
    protected $signature = 'demo:create-data';

    protected $description = 'Crea datos de prueba';

    public function handle(): int
    {
        $this->createDefaultContracts();

        return self::SUCCESS;
    }

    private function createDefaultContracts(): void
    {
        $contracts = json_decode(
            file_get_contents(database_path('data/contracts.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($contracts as $contract) {
            Contract::query()->create($contract);
        }

        $this->info('Contratos demo creados.');
    }
}
