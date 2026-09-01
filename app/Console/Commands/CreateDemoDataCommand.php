<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDemoDataCommand extends Command
{
    protected $signature = 'demo:create-data';

    protected $description = 'Crea datos de prueba';

    public function handle(): int
    {
        $this->createDefaultZones();
        $this->createDefaultContracts();
        $this->createDefaultContractRpbiProfiles();
        $this->createDefaultClients();

        return self::SUCCESS;
    }

    private function createDefaultZones(): void
    {
        $zones = json_decode(
            file_get_contents(database_path('data/zones.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($zones as $zone) {
            Zone::query()->create($zone);
        }

        $this->info('Zonas demo creadas.');
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

    private function createDefaultContractRpbiProfiles(): void
    {
        $rows = json_decode(
            file_get_contents(database_path('data/contract_rpbi_profiles.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $now = now();

        foreach ($rows as $row) {
            DB::table('contract_rpbi_profiles')->insert([
                ...$row,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->info('Perfiles RPBI de contratos demo creados.');
    }

    private function createDefaultClients(): void
    {
        $clients = json_decode(
            file_get_contents(database_path('data/clients.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($clients as $client) {
            Client::query()->create($client);
        }

        $this->info('Clientes demo creados.');
    }
}
