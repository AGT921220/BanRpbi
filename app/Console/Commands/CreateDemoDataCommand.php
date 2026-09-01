<?php

namespace App\Console\Commands;

use App\Features\Permissions\Constants\RoleTypes;
use App\Features\Services\Application\BulkCreateServices;
use App\Models\Client;
use App\Models\ClientConfigurationApproval;
use App\Models\ClientContract;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDemoDataCommand extends Command
{
    protected $signature = 'demo:create-data';

    protected $description = 'Crea datos de prueba';

    private const CLIENTS_PER_WEEKDAY = 10;

    public function handle(BulkCreateServices $bulkCreateServices): int
    {
        $this->createDefaultZones();
        $this->createDefaultContracts();
        $this->createDefaultContractRpbiProfiles();
        $this->createDefaultClients();
        $this->approveClientsAndGenerateServices($bulkCreateServices);

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
        $templates = json_decode(
            file_get_contents(database_path('data/clients.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $template = $templates[0];
        $totalClients = self::CLIENTS_PER_WEEKDAY * 5;

        for ($number = 1; $number <= $totalClients; $number++) {
            Client::query()->create([
                ...$template,
                'name' => "Cliente {$number}",
                'parentarl_surname' => "Prueba {$number}",
                'email' => "cliente{$number}@example.com",
                'phone' => '+52664'.str_pad((string) $number, 7, '0', STR_PAD_LEFT),
                'company' => sprintf('CLIENTE %02d', $number),
                'nra' => sprintf('NRA%06d', $number),
                'configuration_status' => Client::STATUS_CONFIGURATION_PENDING,
                'configuration_submitted_at' => null,
                'configuration_reviewed_at' => null,
                'configuration_rejection_reason' => null,
            ]);
        }

        $this->info("{$totalClients} clientes demo creados (".self::CLIENTS_PER_WEEKDAY.' por día hábil).');
    }

    private function approveClientsAndGenerateServices(BulkCreateServices $bulkCreateServices): void
    {
        $admin = User::role(RoleTypes::ADMIN)->first();
        $directorGeneral = User::role(RoleTypes::DIRECTOR_GENERAL)->first();
        $ventas = User::role(RoleTypes::VENTAS)->first();

        if ($admin === null || $directorGeneral === null) {
            $this->warn('Contratos demo omitidos: ejecuta users:create-demo antes.');

            return;
        }

        $endDate = '2027-09-01';
        $weekStart = Carbon::parse('2026-09-01')->startOfWeek(Carbon::MONDAY);
        $now = now();

        foreach (Client::query()->orderBy('id')->get() as $index => $client) {
            $contract = Contract::query()->find(($index % 2) + 1);

            if ($contract === null) {
                continue;
            }

            $startDate = $weekStart->copy()->addDays($index % 5)->toDateString();

            $clientContract = ClientContract::query()->create([
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'user_id' => $ventas?->id ?? $admin->id,
                'status' => ClientContract::STATUS_ACTIVE,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $contract->cost,
                'generate_initial_invoice' => true,
                'initial_invoice_manifest_count' => 10,
            ]);

            $client->update([
                'configuration_status' => Client::STATUS_APPROVED,
                'configuration_submitted_at' => $now,
                'configuration_reviewed_at' => $now,
            ]);

            ClientConfigurationApproval::query()->create([
                'client_id' => $client->id,
                'client_contract_id' => $clientContract->id,
                'user_id' => $directorGeneral->id,
                'role_name' => RoleTypes::DIRECTOR_GENERAL,
                'approved_at' => $now,
            ]);

            ClientConfigurationApproval::query()->create([
                'client_id' => $client->id,
                'client_contract_id' => $clientContract->id,
                'user_id' => $admin->id,
                'role_name' => RoleTypes::ADMIN,
                'approved_at' => $now,
            ]);

            $client->refresh();
            $bulkCreateServices($client);

            $this->createFakeInvoiceForClient(
                $client,
                $clientContract->initial_invoice_manifest_count ?? 10,
            );
        }

        $this->info('Contratos, aprobaciones, recolecciones y facturas demo creados.');
    }

    private function createFakeInvoiceForClient(Client $client, int $manifestCount): void
    {
        $externalId = Str::random(12);

        $invoice = Invoice::query()->create([
            'client_id' => $client->id,
            'external_id' => $externalId,
            'status' => 'completed',
            'verification_url' => "https://fake.facturaelectronica.sat.gob.mx/default.aspx?id={$externalId}&re=XIA190128J61&rr=GARU861010CP0&tt=0.000000&fe=SeKylw==",
            'sello_cfdi' => 'DKhIwXjfp8X1O0NKSV8BC86psaZ1jIvvkvNYZJWTS2Kkza0q60rIbNsBgm296rdbfyLvtaDBA5MWMHZwV8ANz1bup16AY9nT9uZJ40UT32Cv4UOT6EoBuTcP8gM40rtqTyLsjNrhGD9Ky4An6SIQNGddef0pmhixqfRxKFfosrmQATcZRUaouMFPCbWMh70tgIZIBM69eJGB',
            'sello_sat' => 'VFBGSjdkNHZDd0FPQW1QNWgrUkYwd3ZFSTZvPQ==',
            'cadena_complemento' => '||1.1|802821f0-43ce-4c27-af43-42e5eb48d354|2023-07-03T19:54:44|RmRLxgXo8cJhEEHjglEG6CloJIEf5TzHrgUvL59Bg7pHc5Fc9wf+EUE2za/dS0nRFufQHXxvHWb7oQw/goUz8c1oazd8gGhg/zMkC87KT1alhF7HVh2abUuV3PKCU6PcoBPeYw==|20001000000300022323|| aleatorio',
            'serie_sat' => Str::random(20),
        ]);

        Service::query()
            ->where('client_id', $client->id)
            ->whereNull('invoice_id')
            ->orderBy('id')
            ->limit($manifestCount)
            ->update(['invoice_id' => $invoice->id]);
    }
}
