<?php

namespace App\Console\Commands;

use App\Features\Permissions\Constants\RoleTypes;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Service;
use App\Models\User;
use App\Models\WasteProcessType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Genera datos masivos de prueba para los módulos reales del proyecto.
 *
 * Los datos generados son identificables y borrables con --truncate:
 *   - users / clients ....... correo con dominio @example.test
 *   - contracts / zones / reactors ... nombre con prefijo "[TD] "
 *   - client_contracts / waste_processes ... notes o comments con prefijo "[test-data]"
 *   - invoices .............. external_id con prefijo "TD-"
 *   - services, manifests, service_details, certificados y folios
 *     se borran por su relación con esos registros.
 *
 * Solo funciona en local o testing. Nunca en production/staging (ni con --force).
 */
class GenerateTestDataCommand extends Command
{
    protected $signature = 'app:generate-test-data
        {--scale=medium : Volumen tiny, small, medium, large o stress}
        {--truncate : Eliminar primero los datos de prueba generados anteriormente}
        {--force : No pedir confirmación}
        {--seed= : Semilla aleatoria para resultados reproducibles}
        {--chunk=500 : Tamaño de lote para inserciones}
        {--module=* : Generar solamente módulos específicos}';

    protected $description = 'Genera datos masivos de prueba (solo local/testing) para rendimiento, DataTables, filtros y reportes';

    private const EMAIL_DOMAIN = 'example.test';

    private const NAME_PREFIX = '[TD] ';

    private const NOTES_PREFIX = '[test-data]';

    private const MODULES = [
        'users',
        'zones',
        'clients',
        'contracts',
        'contract_rpbi_profiles',
        'client_contracts',
        'drivers',
        'reactors',
        'services',
        'service_details',
        'manifests',
        'invoices',
        'waste_process_types',
        'waste_processes',
        'waste_process_details',
        'process_certificates',
        'process_folios',
        'client_configuration_approvals',
    ];

    /** Cantidades por escala y módulo. */
    private const SCALES = [
        'tiny' => [
            'users' => 5, 'zones' => 3, 'clients' => 10, 'contracts' => 5,
            'contract_rpbi_profiles' => 10, 'client_contracts' => 15, 'drivers' => 2,
            'reactors' => 2, 'services' => 30, 'service_details' => 60, 'manifests' => 18,
            'invoices' => 8, 'waste_process_types' => 2, 'waste_processes' => 4,
            'waste_process_details' => 12, 'process_certificates' => 3, 'process_folios' => 3,
            'client_configuration_approvals' => 5,
        ],
        'small' => [
            'users' => 20, 'zones' => 20, 'clients' => 200, 'contracts' => 30,
            'contract_rpbi_profiles' => 60, 'client_contracts' => 300, 'drivers' => 8,
            'reactors' => 6, 'services' => 500, 'service_details' => 1_000, 'manifests' => 300,
            'invoices' => 80, 'waste_process_types' => 2, 'waste_processes' => 40,
            'waste_process_details' => 120, 'process_certificates' => 25, 'process_folios' => 25,
            'client_configuration_approvals' => 50,
        ],
        'medium' => [
            'users' => 100, 'zones' => 80, 'clients' => 2_000, 'contracts' => 100,
            'contract_rpbi_profiles' => 250, 'client_contracts' => 4_000, 'drivers' => 40,
            'reactors' => 12, 'services' => 4_000, 'service_details' => 8_000, 'manifests' => 2_500,
            'invoices' => 700, 'waste_process_types' => 2, 'waste_processes' => 250,
            'waste_process_details' => 800, 'process_certificates' => 150, 'process_folios' => 150,
            'client_configuration_approvals' => 400,
        ],
        'large' => [
            'users' => 500, 'zones' => 200, 'clients' => 15_000, 'contracts' => 300,
            'contract_rpbi_profiles' => 800, 'client_contracts' => 30_000, 'drivers' => 150,
            'reactors' => 24, 'services' => 20_000, 'service_details' => 40_000, 'manifests' => 12_000,
            'invoices' => 4_000, 'waste_process_types' => 2, 'waste_processes' => 1_000,
            'waste_process_details' => 4_000, 'process_certificates' => 600, 'process_folios' => 600,
            'client_configuration_approvals' => 2_000,
        ],
        'stress' => [
            'users' => 2_000, 'zones' => 500, 'clients' => 50_000, 'contracts' => 500,
            'contract_rpbi_profiles' => 1_500, 'client_contracts' => 100_000, 'drivers' => 400,
            'reactors' => 40, 'services' => 40_000, 'service_details' => 80_000, 'manifests' => 25_000,
            'invoices' => 8_000, 'waste_process_types' => 2, 'waste_processes' => 2_000,
            'waste_process_details' => 8_000, 'process_certificates' => 1_200, 'process_folios' => 1_200,
            'client_configuration_approvals' => 5_000,
        ],
    ];

    private const SERVICE_STATUSES = [
        Service::STATUS_PENDING => 20,
        Service::STATUS_SCHEDULED => 20,
        Service::STATUS_COLLECTED => 35,
        Service::STATUS_IN_WAREHOUSE => 20,
        Service::STATUS_NEXT => 5,
    ];

    private const RPBI_PROFILES = [
        ['code' => 'BI1', 'name' => 'Cultivos y cepas', 'description' => 'Cultivos, cepas y material biológico-infeccioso.'],
        ['code' => 'BI2', 'name' => 'Objetos punzocortantes', 'description' => 'Objetos capaces de cortar o perforar contaminados.'],
        ['code' => 'BI3', 'name' => 'Residuos patológicos', 'description' => 'Tejidos, órganos y partes anatómicas.'],
        ['code' => 'BI4', 'name' => 'Residuos no anatómicos', 'description' => 'Material desechable contaminado con fluidos.'],
        ['code' => 'BI5', 'name' => 'Sangre', 'description' => 'Sangre humana y sus componentes en forma líquida.'],
    ];

    private string $batch = '';

    private int $chunkSize = 500;

    /** @var list<int> */
    private array $batchUserIds = [];

    /** @var list<int> */
    private array $batchClientIds = [];

    /** @var list<int> */
    private array $batchContractIds = [];

    /** @var list<int> */
    private array $batchZoneIds = [];

    /** @var list<int> */
    private array $batchDriverIds = [];

    /** @var list<int> */
    private array $batchReactorIds = [];

    /** @var list<int> */
    private array $batchWasteProcessIds = [];

    private ?int $folioFrom = null;

    private ?int $folioTo = null;

    public function handle(): int
    {
        // Bloqueo de ambiente: --force NUNCA lo omite.
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Este comando solo puede ejecutarse en local o testing.');

            return self::FAILURE;
        }

        $scale = (string) $this->option('scale');

        if (! array_key_exists($scale, self::SCALES)) {
            $this->error('Escala inválida. Usa: '.implode(', ', array_keys(self::SCALES)).'.');

            return self::FAILURE;
        }

        $modules = $this->option('module') !== []
            ? array_values(array_unique((array) $this->option('module')))
            : self::MODULES;

        $invalid = array_diff($modules, self::MODULES);

        if ($invalid !== []) {
            $this->error('Módulos inválidos: '.implode(', ', $invalid).'. Permitidos: '.implode(', ', self::MODULES).'.');

            return self::FAILURE;
        }

        if (
            ! $this->option('force')
            && ! $this->confirm("¿Deseas generar datos masivos de prueba (escala: {$scale})?")
        ) {
            return self::SUCCESS;
        }

        $seed = $this->option('seed') !== null
            ? (int) $this->option('seed')
            : random_int(1, PHP_INT_MAX - 1);

        mt_srand($seed);
        fake()->seed($seed);

        $this->batch = substr(md5((string) $seed.'|'.Str::uuid()), 0, 8);
        $this->chunkSize = max(50, min(2_000, (int) $this->option('chunk')));

        $startedAt = microtime(true);

        try {
            if ($this->option('truncate')) {
                $this->truncateTestData();
            }

            $counts = [];

            foreach (self::MODULES as $module) {
                if (! in_array($module, $modules, true)) {
                    continue;
                }

                $counts[$module] = match ($module) {
                    'users' => $this->generateUsers(self::SCALES[$scale]['users']),
                    'zones' => $this->generateZones(self::SCALES[$scale]['zones']),
                    'clients' => $this->generateClients(self::SCALES[$scale]['clients']),
                    'contracts' => $this->generateContracts(self::SCALES[$scale]['contracts']),
                    'contract_rpbi_profiles' => $this->generateContractRpbiProfiles(self::SCALES[$scale]['contract_rpbi_profiles']),
                    'client_contracts' => $this->generateClientContracts(self::SCALES[$scale]['client_contracts']),
                    'drivers' => $this->generateDrivers(self::SCALES[$scale]['drivers']),
                    'reactors' => $this->generateReactors(self::SCALES[$scale]['reactors']),
                    'services' => $this->generateServices(self::SCALES[$scale]['services']),
                    'service_details' => $this->generateServiceDetails(self::SCALES[$scale]['service_details']),
                    'manifests' => $this->generateManifests(self::SCALES[$scale]['manifests']),
                    'invoices' => $this->generateInvoices(self::SCALES[$scale]['invoices']),
                    'waste_process_types' => $this->generateWasteProcessTypes(),
                    'waste_processes' => $this->generateWasteProcesses(self::SCALES[$scale]['waste_processes']),
                    'waste_process_details' => $this->generateWasteProcessDetails(self::SCALES[$scale]['waste_process_details']),
                    'process_certificates' => $this->generateProcessCertificates(self::SCALES[$scale]['process_certificates']),
                    'process_folios' => $this->generateProcessFolios(self::SCALES[$scale]['process_folios']),
                    'client_configuration_approvals' => $this->generateConfigurationApprovals(self::SCALES[$scale]['client_configuration_approvals']),
                };
            }

            $this->printSummary($counts, $seed, $startedAt);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Error generando datos de prueba: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    // ---------------------------------------------------------------
    // Generadores por módulo
    // ---------------------------------------------------------------

    private function generateUsers(int $total): int
    {
        $this->info("Generando usuarios ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $password = Hash::make('password');
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $createdAt = $this->randomDate(730, 0);

            $rows[] = [
                'name' => fake()->name(),
                'nickname' => "usuario{$i}.{$this->batch}",
                'email' => "usuario{$i}.{$this->batch}@".self::EMAIL_DOMAIN,
                'email_verified_at' => mt_rand(1, 100) <= 90 ? $createdAt : null,
                'password' => $password,
                'remember_token' => Str::random(10),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => mt_rand(1, 100) <= 5 ? $this->randomDate(60, 0) : null,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('users')->insertOrIgnore($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->assignRolesToBatchUsers();
        $this->ensureDemoUser();

        return $total;
    }

    /** Asigna un rol real aleatorio a cada usuario generado en este lote. */
    private function assignRolesToBatchUsers(): void
    {
        $roleIds = Role::query()->where('guard_name', 'web')->pluck('id')->all();

        if ($roleIds === []) {
            $this->warn('No hay roles en la base (ejecuta permissions:create y roles:create). Usuarios sin rol.');

            return;
        }

        $this->batchUserIds = array_map(
            'intval',
            DB::table('users')
                ->where('email', 'like', "%.{$this->batch}@".self::EMAIL_DOMAIN)
                ->pluck('id')
                ->all(),
        );
        $userIds = $this->batchUserIds;

        $rows = [];

        foreach ($userIds as $userId) {
            $rows[] = [
                'role_id' => $roleIds[mt_rand(0, count($roleIds) - 1)],
                'model_type' => User::class,
                'model_id' => $userId,
            ];

            if (count($rows) >= $this->chunkSize) {
                DB::table('model_has_roles')->insertOrIgnore($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('model_has_roles')->insertOrIgnore($rows);
        }
    }

    /** Crea (una sola vez) la cuenta demo para probar el login con datos masivos. */
    private function ensureDemoUser(): void
    {
        $demo = User::query()->withTrashed()->firstOrCreate(
            ['email' => 'admin@'.self::EMAIL_DOMAIN],
            [
                'name' => 'Admin Datos de Prueba',
                'nickname' => 'admin.testdata',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        if ($demo->trashed()) {
            $demo->restore();
        }

        if (Role::query()->where('name', 'Admin')->where('guard_name', 'web')->exists()) {
            $demo->syncRoles(['Admin']);
        }
    }

    private function generateClients(int $total): int
    {
        $this->info("Generando clientes ({$total})...");

        $zoneIds = $this->zonePool();
        $bar = $this->output->createProgressBar($total);
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $createdAt = $this->randomDate(730, 0);
            $status = (string) $this->weighted([
                Client::STATUS_APPROVED => 70,
                Client::STATUS_PENDING_APPROVAL => 15,
                Client::STATUS_CONFIGURATION_PENDING => 10,
                Client::STATUS_REJECTED => 5,
            ]);
            $reviewed = $status === Client::STATUS_APPROVED || $status === Client::STATUS_REJECTED;

            $rows[] = [
                'name' => fake()->firstName(),
                'parentarl_surname' => fake()->lastName(),
                'email' => "cliente{$i}.{$this->batch}@".self::EMAIL_DOMAIN,
                'phone' => '81'.str_pad((string) mt_rand(0, 99_999_999), 8, '0', STR_PAD_LEFT),
                'company' => fake()->company(),
                'configuration_status' => $status,
                'nra' => fake()->bothify('NRA-########'),
                'rfc' => strtoupper(fake()->lexify('????').fake()->numerify('######').fake()->bothify('???')),
                'street' => fake()->streetName(),
                'num_ext' => (string) mt_rand(1, 9999),
                'num_int' => mt_rand(1, 100) <= 40 ? (string) mt_rand(1, 50) : null,
                'postal_code' => str_pad((string) mt_rand(1000, 99999), 5, '0', STR_PAD_LEFT),
                'maps_url' => null,
                'maps_place_id' => null,
                'latitude' => null,
                'longitude' => null,
                'zone_id' => $zoneIds === [] ? null : $zoneIds[mt_rand(0, count($zoneIds) - 1)],
                'configuration_submitted_at' => $status === Client::STATUS_CONFIGURATION_PENDING ? null : $createdAt,
                'configuration_reviewed_at' => $reviewed ? $createdAt : null,
                'configuration_rejection_reason' => $status === Client::STATUS_REJECTED ? 'Documentación incompleta' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('clients')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->batchClientIds = $this->idsWhere(
            'clients',
            'email',
            "%.{$this->batch}@".self::EMAIL_DOMAIN,
        );

        return $total;
    }

    private function generateContracts(int $total): int
    {
        $this->info("Generando contratos ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $types = ['estándar RPBI', 'anual de recolección', 'especial industrial', 'básico', 'hospitalario', 'laboratorio'];
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $createdAt = $this->randomDate(730, 0);

            $rows[] = [
                'name' => self::NAME_PREFIX.'Contrato '.$types[mt_rand(0, count($types) - 1)]." {$this->batch}-{$i}",
                'notes' => mt_rand(1, 100) <= 60 ? fake()->sentence() : null,
                'duration_months' => [3, 6, 12, 12, 12, 24, 36][mt_rand(0, 6)],
                'frequency' => $this->weighted(['monthly' => 50, 'biweekly' => 30, 'weekly' => 20]),
                'cost' => $this->amount(800, 28_000),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => mt_rand(1, 100) <= 5 ? $this->randomDate(90, 0) : null,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('contracts')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->batchContractIds = $this->idsWhere(
            'contracts',
            'name',
            self::NAME_PREFIX.'%'.$this->batch.'-%',
        );

        return $total;
    }

    private function generateZones(int $total): int
    {
        $this->info("Generando zonas ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22'];
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $createdAt = $this->randomDate(730, 0);

            // Polígono pequeño alrededor del área de Monterrey.
            $lng = -100.5 + mt_rand(0, 4_000) / 10_000;
            $lat = 25.5 + mt_rand(0, 3_000) / 10_000;

            $rows[] = [
                'name' => self::NAME_PREFIX."Zona {$this->batch}-{$i}",
                'description' => mt_rand(1, 100) <= 50 ? fake()->sentence() : null,
                'color' => $colors[mt_rand(0, count($colors) - 1)],
                'geometry' => json_encode([
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [$lng, $lat],
                        [$lng + 0.02, $lat],
                        [$lng + 0.02, $lat + 0.02],
                        [$lng, $lat + 0.02],
                        [$lng, $lat],
                    ]],
                ]),
                'is_active' => mt_rand(1, 100) <= 85 ? 1 : 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('zones')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->batchZoneIds = $this->idsWhere(
            'zones',
            'name',
            self::NAME_PREFIX."Zona {$this->batch}-%",
        );

        return $total;
    }

    private function generateClientContracts(int $total): int
    {
        // Pools precargados: cero consultas por registro.
        $clientIds = Client::query()->pluck('id')->all();
        $contracts = Contract::query()->withTrashed()->get(['id', 'duration_months', 'cost'])->keyBy('id');
        $contractIds = $contracts->keys()->all();
        $userIds = DB::table('users')->whereNull('deleted_at')->pluck('id')->all();

        if ($clientIds === [] || $contractIds === []) {
            $this->warn('client_contracts omitido: se necesitan clientes y contratos existentes (genera esos módulos primero).');

            return 0;
        }

        $this->info("Generando asignaciones cliente-contrato ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            // 0=pendiente, 1=activo, 2=completado, 3=cancelado
            $status = (int) $this->weighted([1 => 60, 0 => 20, 2 => 15, 3 => 5]);

            $contractId = $contractIds[mt_rand(0, count($contractIds) - 1)];
            $contract = $contracts[$contractId];
            $durationMonths = max(1, (int) $contract->duration_months);

            // Completados: inicio suficientemente lejano para que su fin ya haya pasado.
            if ($status === 2) {
                $from = $durationMonths * 30 + 30;
                $startDaysAgo = mt_rand($from, max($from + 90, 1_095));
            } else {
                $startDaysAgo = mt_rand(0, 720);
            }

            $start = now()->subDays($startDaysAgo)->startOfDay();
            $end = $start->copy()->addMonths($durationMonths);
            $createdAt = $start->copy()->subDays(mt_rand(1, 15))->format('Y-m-d H:i:s');

            $rows[] = [
                'client_id' => $clientIds[mt_rand(0, count($clientIds) - 1)],
                'contract_id' => $contractId,
                'user_id' => ($userIds !== [] && mt_rand(1, 100) <= 90)
                    ? $userIds[mt_rand(0, count($userIds) - 1)]
                    : null,
                'notes' => self::NOTES_PREFIX.' '.$this->batch.(mt_rand(1, 100) <= 40 ? ' '.fake()->sentence() : ''),
                'status' => $status,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'price' => $contract->cost,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('client_contracts')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $total;
    }

    private function generateContractRpbiProfiles(int $total): int
    {
        $contractIds = $this->contractPool();
        $profileIds = $this->ensureRpbiProfiles();

        if ($contractIds === [] || $profileIds === []) {
            $this->warn('Perfiles de contrato omitidos: se necesitan contratos de prueba y perfiles RPBI.');

            return 0;
        }

        $this->info("Generando perfiles RPBI de contratos ({$total})...");

        $rows = [];
        $created = 0;
        $now = now()->format('Y-m-d H:i:s');
        $profileCount = count($profileIds);

        foreach ($contractIds as $contractId) {
            $start = mt_rand(0, $profileCount - 1);
            $take = mt_rand(2, $profileCount);

            for ($offset = 0; $offset < $take; $offset++) {
                if ($created >= $total) {
                    break 2;
                }

                $rows[] = [
                    'contract_id' => $contractId,
                    'rpbi_profile_id' => $profileIds[($start + $offset) % $profileCount],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $created++;
            }
        }

        foreach (array_chunk($rows, $this->chunkSize) as $chunk) {
            DB::table('contract_rpbi_profiles')->insertOrIgnore($chunk);
        }

        $this->line("  relaciones: {$created}");

        return $created;
    }

    private function generateDrivers(int $total): int
    {
        $userIds = $this->availableDriverUserIds($total);

        if ($userIds === []) {
            $this->warn('Choferes omitidos: no hay usuarios de prueba libres.');

            return 0;
        }

        $count = count($userIds);

        if ($count < $total) {
            $this->warn("Choferes limitados a {$count} usuarios disponibles.");
        }

        $this->info("Generando choferes ({$count})...");

        $bar = $this->output->createProgressBar($count);
        $rows = [];
        $roleRows = [];
        $choferRoleId = Role::query()
            ->where('name', RoleTypes::CHOFER)
            ->where('guard_name', 'web')
            ->value('id');
        $now = now()->format('Y-m-d H:i:s');

        foreach ($userIds as $index => $userId) {
            $rows[] = [
                'name' => fake()->firstName(),
                'parentarl_surname' => fake()->lastName(),
                'maternal_surname' => fake()->lastName(),
                'phone' => '81'.str_pad((string) mt_rand(0, 99_999_999), 8, '0', STR_PAD_LEFT),
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($choferRoleId !== null) {
                $roleRows[] = [
                    'role_id' => $choferRoleId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ];
            }

            if (count($rows) >= $this->chunkSize || $index === $count - 1) {
                DB::table('drivers')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        foreach (array_chunk($roleRows, $this->chunkSize) as $chunk) {
            DB::table('model_has_roles')->insertOrIgnore($chunk);
        }

        $bar->finish();
        $this->newLine();

        $this->batchDriverIds = array_map(
            'intval',
            DB::table('drivers')->whereIn('user_id', $userIds)->pluck('id')->all(),
        );

        return $count;
    }

    private function generateReactors(int $total): int
    {
        $this->info("Generando reactores ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $rows = [];
        $now = now()->format('Y-m-d H:i:s');

        for ($i = 1; $i <= $total; $i++) {
            $rows[] = [
                'name' => self::NAME_PREFIX."Reactor {$this->batch}-{$i}",
                'status' => mt_rand(1, 100) <= 85 ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('reactors')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->batchReactorIds = $this->idsWhere(
            'reactors',
            'name',
            self::NAME_PREFIX."Reactor {$this->batch}-%",
        );

        return $total;
    }

    private function generateServices(int $total): int
    {
        $clientIds = $this->clientPool();
        $zoneIds = $this->zonePool();
        $contractIds = $this->contractPool();

        if ($clientIds === [] || $zoneIds === [] || $contractIds === []) {
            $this->warn('Servicios omitidos: se necesitan clientes, zonas y contratos de prueba.');

            return 0;
        }

        $driverIds = $this->driverPool();
        $userIds = $this->userPool();
        $costs = DB::table('contracts')->whereIn('id', $contractIds)->pluck('cost', 'id');
        $folio = (int) DB::table('services')->max('folio');
        $this->folioFrom = $folio + 1;

        $this->info("Generando servicios ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $folio++;
            $status = (string) $this->weighted(self::SERVICE_STATUSES);
            $contractId = $contractIds[mt_rand(0, count($contractIds) - 1)];
            $createdAt = $this->randomDate(540, 0);
            $inWarehouse = $status === Service::STATUS_IN_WAREHOUSE;

            $rows[] = [
                'service_date' => now()->subDays(mt_rand(0, 540))->toDateString(),
                'zone_id' => $zoneIds[mt_rand(0, count($zoneIds) - 1)],
                'client_id' => $clientIds[mt_rand(0, count($clientIds) - 1)],
                'contract_id' => $contractId,
                'driver_id' => ($driverIds !== [] && mt_rand(1, 100) <= 85)
                    ? $driverIds[mt_rand(0, count($driverIds) - 1)]
                    : null,
                'cost' => $costs[$contractId] ?? 0,
                'status' => $status,
                'warehouse_entry_at' => $inWarehouse ? $createdAt : null,
                'warehouse_entry_user_id' => ($inWarehouse && $userIds !== [])
                    ? $userIds[mt_rand(0, count($userIds) - 1)]
                    : null,
                'route_order' => mt_rand(1, 40),
                'folio' => $folio,
                'order' => $i,
                'invoice_id' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('services')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->folioTo = $folio;

        return $total;
    }

    private function generateServiceDetails(int $total): int
    {
        $serviceIds = $this->scopedServiceIds();
        $profileIds = $this->ensureRpbiProfiles();

        if ($serviceIds === [] || $profileIds === []) {
            $this->warn('Detalles de servicio omitidos: se necesitan servicios de prueba y perfiles RPBI.');

            return 0;
        }

        $this->info("Generando detalles de servicio ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $rows = [];
        $serviceCount = count($serviceIds);
        $profileCount = count($profileIds);
        $now = now()->format('Y-m-d H:i:s');

        for ($i = 1; $i <= $total; $i++) {
            $rows[] = [
                'service_id' => $serviceIds[($i - 1) % $serviceCount],
                'weight' => $this->amount(1, 120),
                'rpbi_profile_id' => $profileIds[($i - 1) % $profileCount],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('service_details')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $total;
    }

    private function generateManifests(int $total): int
    {
        $serviceIds = $this->manifestServiceIds($total);
        $count = count($serviceIds);

        if ($count === 0) {
            $this->warn('Manifiestos omitidos: no hay servicios de prueba sin manifiesto.');

            return 0;
        }

        $this->info("Generando manifiestos ({$count})...");

        $bar = $this->output->createProgressBar($count);
        $rows = [];
        $now = now()->format('Y-m-d H:i:s');

        foreach ($serviceIds as $index => $serviceId) {
            $rows[] = [
                'public_uuid' => (string) Str::uuid(),
                'service_id' => $serviceId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if (count($rows) >= $this->chunkSize || $index === $count - 1) {
                DB::table('manifests')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    private function generateInvoices(int $total): int
    {
        $clientIds = $this->clientPool();

        if ($clientIds === []) {
            $this->warn('Facturas omitidas: no hay clientes de prueba.');

            return 0;
        }

        $this->info("Generando facturas ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $rows = [];
        $clientCount = count($clientIds);

        for ($i = 1; $i <= $total; $i++) {
            $createdAt = $this->randomDate(400, 0);
            $status = (string) $this->weighted(['pending' => 35, 'completed' => 55, 'cancelled' => 10]);
            $completed = $status === 'completed';

            $rows[] = [
                'client_id' => $clientIds[($i - 1) % $clientCount],
                'external_id' => "TD-{$this->batch}-{$i}",
                'status' => $status,
                'verification_url' => $completed ? 'https://example.test/cfdi/'.$this->batch.'/'.$i : null,
                'sello_cfdi' => null,
                'sello_sat' => null,
                'cadena_complemento' => null,
                'serie_sat' => $completed ? 'A' : null,
                'qr_code' => null,
                'iva_percentaje' => '16',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('invoices')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->linkInvoicesToServices();

        return $total;
    }

    private function generateWasteProcessTypes(): int
    {
        $now = now()->format('Y-m-d H:i:s');
        $types = [
            WasteProcessType::PROCESS_INCINERACION => 'Incineración',
            WasteProcessType::PROCESS_ESTERILIZACION => 'Esterilización',
        ];

        foreach ($types as $id => $name) {
            if (DB::table('waste_process_types')->where('id', $id)->exists()) {
                continue;
            }

            DB::table('waste_process_types')->insert([
                'id' => $id,
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->line('Tipos de proceso asegurados: '.count($types));

        return count($types);
    }

    private function generateWasteProcesses(int $total): int
    {
        $this->generateWasteProcessTypes();

        $userIds = $this->userPool();
        $reactorIds = $this->reactorPool();

        if ($userIds === []) {
            $userIds = array_map('intval', DB::table('users')->whereNull('deleted_at')->pluck('id')->all());
        }

        $this->info("Generando procesos de residuo ({$total})...");

        $bar = $this->output->createProgressBar($total);
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $typeId = mt_rand(1, 100) <= 50
                ? WasteProcessType::PROCESS_INCINERACION
                : WasteProcessType::PROCESS_ESTERILIZACION;
            $rows[] = $this->wasteProcessRow($typeId, $userIds, $reactorIds);

            if (count($rows) >= $this->chunkSize || $i === $total) {
                DB::table('waste_processes')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        $this->batchWasteProcessIds = array_map('intval', DB::table('waste_processes')
            ->where('comments', self::NOTES_PREFIX.' '.$this->batch)
            ->pluck('id')
            ->all());

        return $total;
    }

    private function generateWasteProcessDetails(int $total): int
    {
        $processIds = $this->wasteProcessPool();
        $pairs = $this->manifestDetailPairs($total);

        if ($processIds === [] || $pairs === []) {
            $this->warn('Detalles de proceso omitidos: se necesitan procesos, manifiestos y detalles de servicio de prueba.');

            return 0;
        }

        $count = min($total, max(count($pairs), 0));

        if (count($pairs) < $total) {
            $this->warn('Detalles de proceso limitados a '.count($pairs).' pares manifiesto/detalle.');
            $count = count($pairs);
        }

        $this->info("Generando detalles de proceso ({$count})...");

        $bar = $this->output->createProgressBar($count);
        $rows = [];
        $processCount = count($processIds);
        $now = now()->format('Y-m-d H:i:s');
        $userIds = $this->userPool();

        for ($i = 0; $i < $count; $i++) {
            $pair = $pairs[$i];
            $totalWeight = is_numeric($pair->weight) ? (string) $pair->weight : $this->amount(1, 80);
            $finished = mt_rand(1, 100) <= 40;

            $rows[] = [
                'waste_process_id' => $processIds[$i % $processCount],
                'manifest_id' => $pair->manifest_id,
                'service_detail_id' => $pair->service_detail_id,
                'total_weight' => $totalWeight,
                'process_weight' => number_format(((float) $totalWeight) * mt_rand(40, 100) / 100, 2, '.', ''),
                'is_finished' => $finished ? 1 : 0,
                'is_completed' => $finished && mt_rand(1, 100) <= 70 ? 1 : 0,
                'finished_by_id' => ($finished && $userIds !== []) ? $userIds[mt_rand(0, count($userIds) - 1)] : null,
                'finished_at' => $finished ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $this->chunkSize || $i === $count - 1) {
                DB::table('waste_process_details')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    private function generateProcessCertificates(int $total): int
    {
        $processIds = $this->wasteProcessPool();
        $manifests = $this->testManifests($total);

        if ($processIds === [] || $manifests === []) {
            $this->warn('Certificados omitidos: se necesitan procesos y manifiestos de prueba.');

            return 0;
        }

        $count = min($total, count($manifests));
        $this->info("Generando certificados de proceso ({$count})...");

        $bar = $this->output->createProgressBar($count);
        $rows = [];
        $processCount = count($processIds);
        $now = now()->format('Y-m-d H:i:s');

        for ($i = 0; $i < $count; $i++) {
            $manifest = $manifests[$i];
            $rows[] = [
                'waste_process_id' => $processIds[$i % $processCount],
                'client_id' => $manifest->client_id,
                'manifest_id' => $manifest->id,
                'manifest_ids' => (string) $manifest->id,
                'manifest_folios' => self::NOTES_PREFIX.' '.$manifest->id,
                'date' => now()->subDays(mt_rand(0, 180))->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $this->chunkSize || $i === $count - 1) {
                DB::table('process_certificates')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    private function generateProcessFolios(int $total): int
    {
        $certificates = DB::table('process_certificates')
            ->join('waste_processes', 'waste_processes.id', '=', 'process_certificates.waste_process_id')
            ->where('waste_processes.comments', 'like', self::NOTES_PREFIX.' '.$this->batch.'%')
            ->limit($total)
            ->get([
                'process_certificates.id',
                'process_certificates.client_id',
                'process_certificates.waste_process_id',
            ]);

        if ($certificates->isEmpty()) {
            $this->warn('Folios omitidos: no hay certificados de procesos de prueba.');

            return 0;
        }

        $count = min($total, $certificates->count());
        $this->info("Generando folios de proceso ({$count})...");

        $bar = $this->output->createProgressBar($count);
        $rows = [];
        $now = now()->format('Y-m-d H:i:s');

        for ($i = 0; $i < $count; $i++) {
            $certificate = $certificates[$i];
            $rows[] = [
                'client_id' => $certificate->client_id,
                'process_certificate_id' => $certificate->id,
                'waste_process_id' => $certificate->waste_process_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $this->chunkSize || $i === $count - 1) {
                DB::table('process_folios')->insert($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    private function generateConfigurationApprovals(int $total): int
    {
        $contracts = DB::table('client_contracts')
            ->where('notes', 'like', self::NOTES_PREFIX.' '.$this->batch.'%')
            ->whereNotNull('client_id')
            ->limit($total)
            ->get(['id', 'client_id']);

        $userIds = $this->userPool();
        $roleNames = Role::query()->where('guard_name', 'web')->pluck('name')->all();

        if ($contracts->isEmpty() || $userIds === []) {
            $this->warn('Aprobaciones omitidas: se necesitan asignaciones y usuarios de prueba.');

            return 0;
        }

        if ($roleNames === []) {
            $roleNames = [RoleTypes::ADMIN];
        }

        $count = $contracts->count();
        $this->info("Generando aprobaciones de configuración ({$count})...");

        $bar = $this->output->createProgressBar($count);
        $rows = [];
        $now = now()->format('Y-m-d H:i:s');
        $userCount = count($userIds);
        $roleCount = count($roleNames);

        foreach ($contracts as $index => $contract) {
            $rows[] = [
                'client_id' => $contract->client_id,
                'client_contract_id' => $contract->id,
                'user_id' => $userIds[$index % $userCount],
                'role_name' => $roleNames[$index % $roleCount],
                'approved_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $this->chunkSize || $index === $count - 1) {
                DB::table('client_configuration_approvals')->insertOrIgnore($rows);
                $bar->advance(count($rows));
                $rows = [];
            }
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    // ---------------------------------------------------------------
    // Truncado seguro: borra SOLO datos marcados como de prueba
    // ---------------------------------------------------------------

    private function truncateTestData(): void
    {
        $this->info('Eliminando datos de prueba anteriores...');

        $processIds = DB::table('waste_processes')
            ->where('comments', 'like', self::NOTES_PREFIX.'%')
            ->pluck('id');

        $this->line('  process_folios: '.$this->deleteWhereIn('process_folios', 'waste_process_id', $processIds));
        $this->line('  process_certificates: '.$this->deleteWhereIn('process_certificates', 'waste_process_id', $processIds));
        $this->line('  waste_process_details: '.$this->deleteWhereIn('waste_process_details', 'waste_process_id', $processIds));
        $this->line('  waste_processes: '.$this->deleteWhereIn('waste_processes', 'id', $processIds));

        $serviceIds = DB::table('services')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->where('clients.email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->pluck('services.id');

        $this->line('  manifests: '.$this->deleteWhereIn('manifests', 'service_id', $serviceIds));
        $this->line('  service_details: '.$this->deleteWhereIn('service_details', 'service_id', $serviceIds));

        $invoiceIds = DB::table('invoices')->where('external_id', 'like', 'TD-%')->pluck('id');
        $this->nullWhereIn('services', 'invoice_id', $invoiceIds);
        $this->line('  services: '.$this->deleteWhereIn('services', 'id', $serviceIds));
        $this->line('  invoices: '.$this->deleteWhereIn('invoices', 'id', $invoiceIds));

        $clientContractIds = DB::table('client_contracts')
            ->where('notes', 'like', self::NOTES_PREFIX.'%')
            ->pluck('id');
        $this->line('  client_configuration_approvals: '.$this->deleteWhereIn('client_configuration_approvals', 'client_contract_id', $clientContractIds));

        $deleted = DB::table('client_contracts')->where('notes', 'like', self::NOTES_PREFIX.'%')->delete();
        $this->line("  client_contracts: {$deleted}");

        $deleted = DB::table('clients')->where('email', 'like', '%@'.self::EMAIL_DOMAIN)->delete();
        $this->line("  clients: {$deleted}");

        $contractIds = DB::table('contracts')->where('name', 'like', self::NAME_PREFIX.'%')->pluck('id');
        $this->line('  contract_rpbi_profiles: '.$this->deleteWhereIn('contract_rpbi_profiles', 'contract_id', $contractIds));

        $deleted = DB::table('contracts')->where('name', 'like', self::NAME_PREFIX.'%')->delete();
        $this->line("  contracts: {$deleted}");

        $deleted = DB::table('zones')->where('name', 'like', self::NAME_PREFIX.'%')->delete();
        $this->line("  zones: {$deleted}");

        $deleted = DB::table('reactors')->where('name', 'like', self::NAME_PREFIX.'%')->delete();
        $this->line("  reactors: {$deleted}");

        $testUserIds = DB::table('users')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->pluck('id');

        $this->line('  drivers: '.$this->deleteWhereIn('drivers', 'user_id', $testUserIds));

        foreach ($testUserIds->chunk(1_000) as $chunk) {
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('model_id', $chunk)
                ->delete();

            DB::table('model_has_permissions')
                ->where('model_type', User::class)
                ->whereIn('model_id', $chunk)
                ->delete();

            DB::table('users')->whereIn('id', $chunk)->delete();
        }

        $this->line('  users: '.$testUserIds->count());
        $this->newLine();
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /** @return list<int> */
    private function idsWhere(string $table, string $column, string $like): array
    {
        return array_map(
            'intval',
            DB::table($table)->where($column, 'like', $like)->pluck('id')->all(),
        );
    }

    /** @return list<int> */
    private function clientPool(): array
    {
        if ($this->batchClientIds !== []) {
            return $this->batchClientIds;
        }

        return $this->idsWhere('clients', 'email', '%@'.self::EMAIL_DOMAIN);
    }

    /** @return list<int> */
    private function contractPool(): array
    {
        if ($this->batchContractIds !== []) {
            return $this->batchContractIds;
        }

        return $this->idsWhere('contracts', 'name', self::NAME_PREFIX.'%');
    }

    /** @return list<int> */
    private function zonePool(): array
    {
        if ($this->batchZoneIds !== []) {
            return $this->batchZoneIds;
        }

        $ids = $this->idsWhere('zones', 'name', self::NAME_PREFIX.'%');

        if ($ids !== []) {
            return $ids;
        }

        return array_map('intval', DB::table('zones')->pluck('id')->all());
    }

    /** @return list<int> */
    private function userPool(): array
    {
        if ($this->batchUserIds !== []) {
            return $this->batchUserIds;
        }

        return array_map('intval', DB::table('users')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all());
    }

    /** @return list<int> */
    private function driverPool(): array
    {
        if ($this->batchDriverIds !== []) {
            return $this->batchDriverIds;
        }

        $ids = DB::table('drivers')
            ->join('users', 'users.id', '=', 'drivers.user_id')
            ->where('users.email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->pluck('drivers.id')
            ->all();

        if ($ids !== []) {
            return array_map('intval', $ids);
        }

        return array_map('intval', DB::table('drivers')->pluck('id')->all());
    }

    /** @return list<int> */
    private function reactorPool(): array
    {
        if ($this->batchReactorIds !== []) {
            return $this->batchReactorIds;
        }

        $ids = $this->idsWhere('reactors', 'name', self::NAME_PREFIX.'%');

        if ($ids !== []) {
            return $ids;
        }

        return array_map('intval', DB::table('reactors')->pluck('id')->all());
    }

    /** @return list<int> */
    private function wasteProcessPool(): array
    {
        if ($this->batchWasteProcessIds !== []) {
            return $this->batchWasteProcessIds;
        }

        return array_map('intval', DB::table('waste_processes')
            ->where('comments', 'like', self::NOTES_PREFIX.'%')
            ->pluck('id')
            ->all());
    }

    /** @return list<int> */
    private function availableDriverUserIds(int $total): array
    {
        $batchIds = DB::table('users')
            ->where('email', 'like', "%.{$this->batch}@".self::EMAIL_DOMAIN)
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('drivers');
            })
            ->limit($total)
            ->pluck('id')
            ->all();

        if ($batchIds !== []) {
            return array_map('intval', $batchIds);
        }

        return array_map('intval', DB::table('users')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('drivers');
            })
            ->limit($total)
            ->pluck('id')
            ->all());
    }

    private function scopedServices()
    {
        $query = DB::table('services')->whereNull('services.deleted_at');

        if ($this->folioFrom !== null && $this->folioTo !== null) {
            return $query->whereBetween('services.folio', [$this->folioFrom, $this->folioTo]);
        }

        return $query
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->where('clients.email', 'like', '%@'.self::EMAIL_DOMAIN);
    }

    /** @return list<int> */
    private function scopedServiceIds(): array
    {
        return array_map('intval', $this->scopedServices()->pluck('services.id')->all());
    }

    /** @return list<int> */
    private function manifestServiceIds(int $total): array
    {
        $preferred = $this->servicesWithoutManifest()
            ->whereIn('services.status', [
                Service::STATUS_COLLECTED,
                Service::STATUS_IN_WAREHOUSE,
                Service::STATUS_SCHEDULED,
            ])
            ->orderBy('services.id')
            ->limit($total)
            ->pluck('services.id')
            ->all();

        if (count($preferred) >= $total) {
            return array_map('intval', $preferred);
        }

        $extra = $this->servicesWithoutManifest()
            ->when($preferred !== [], function ($query) use ($preferred) {
                $query->whereNotIn('services.id', $preferred);
            })
            ->orderBy('services.id')
            ->limit($total - count($preferred))
            ->pluck('services.id')
            ->all();

        return array_map('intval', array_merge($preferred, $extra));
    }

    private function servicesWithoutManifest()
    {
        return $this->scopedServices()
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('manifests')
                    ->whereColumn('manifests.service_id', 'services.id')
                    ->whereNull('manifests.deleted_at');
            });
    }

    private function linkInvoicesToServices(): void
    {
        $invoiceIds = array_map('intval', DB::table('invoices')
            ->where('external_id', 'like', "TD-{$this->batch}-%")
            ->orderBy('id')
            ->pluck('id')
            ->all());

        if ($invoiceIds === []) {
            return;
        }

        $serviceIds = array_map('intval', $this->scopedServices()
            ->whereNull('services.invoice_id')
            ->whereIn('services.status', [Service::STATUS_COLLECTED, Service::STATUS_IN_WAREHOUSE])
            ->orderBy('services.id')
            ->limit(count($invoiceIds) * 3)
            ->pluck('services.id')
            ->all());

        if ($serviceIds === []) {
            return;
        }

        $this->line('Vinculando facturas con servicios recolectados...');

        foreach (array_chunk($serviceIds, 3) as $index => $chunk) {
            if (! array_key_exists($index, $invoiceIds)) {
                return;
            }

            DB::table('services')->whereIn('id', $chunk)->update([
                'invoice_id' => $invoiceIds[$index],
            ]);
        }
    }

    /**
     * @param  list<int>  $userIds
     * @param  list<int>  $reactorIds
     * @return array<string, mixed>
     */
    private function wasteProcessRow(int $typeId, array $userIds, array $reactorIds): array
    {
        $finished = mt_rand(1, 100) <= 45;
        $createdAt = $this->randomDate(400, 0);
        $userId = $userIds === [] ? null : $userIds[mt_rand(0, count($userIds) - 1)];
        $incineracion = $typeId === WasteProcessType::PROCESS_INCINERACION;

        return [
            'user_id' => $userId,
            'waste_process_type_id' => $typeId,
            'date_start' => substr($createdAt, 0, 10),
            'hour_start_machine' => sprintf('%02d:%02d:00', mt_rand(6, 9), mt_rand(0, 59)),
            'hour_machine' => sprintf('%02d:%02d:00', mt_rand(10, 18), mt_rand(0, 59)),
            'machine_number' => $reactorIds === [] ? null : $reactorIds[mt_rand(0, count($reactorIds) - 1)],
            'solution' => $incineracion ? 'Incineración RPBI' : 'Esterilización RPBI',
            'comments' => self::NOTES_PREFIX.' '.$this->batch,
            'cantidad_ingresado' => $this->amount(10, 800),
            'others' => $this->amount(0, 20),
            'psi' => $this->amount(10, 40),
            'is_finished' => $finished ? 1 : 0,
            'finished_by_id' => $finished ? $userId : null,
            'finished_at' => $finished ? $createdAt : null,
            'temp_camara_primaria' => $incineracion ? $this->amount(700, 1_100) : null,
            'temp_camara_secundaria' => $incineracion ? $this->amount(800, 1_200) : null,
            'temp_entrada_venturi' => $incineracion ? $this->amount(150, 400) : null,
            'temp_salida_venturi' => $incineracion ? $this->amount(80, 200) : null,
            'temp_salida_chimenea' => $incineracion ? $this->amount(60, 180) : null,
            'oxigeno' => $incineracion ? $this->amount(6, 12) : null,
            'presion_negativa' => $incineracion ? $this->amount(1, 8) : null,
            'consumo_gas_lp' => $incineracion ? $this->amount(20, 180) : null,
            'consumo_promedio_agua' => $incineracion ? $this->amount(10, 90) : null,
            'evaporacion_agua_tratada' => $incineracion ? $this->amount(5, 40) : null,
            'patologicos' => $incineracion ? $this->amount(1, 80) : null,
            'cultivos_cepas' => $incineracion ? $this->amount(1, 40) : null,
            'sangre' => $incineracion ? $this->amount(1, 50) : null,
            'ceniza_generada' => $incineracion ? $this->amount(1, 25) : null,
            'descarga_lavador' => $incineracion ? $this->amount(1, 30) : null,
            'temp_esterilizador' => $incineracion ? null : $this->amount(120, 140),
            'presion_camara_principal' => $incineracion ? null : $this->amount(15, 35),
            'eliminacion_biologica' => $incineracion ? null : 'Completa',
            'ciclo_carga' => $incineracion ? null : 'Ciclo '.mt_rand(1, 4),
            'rpbi_bi1_cepas' => $incineracion ? null : $this->amount(1, 40),
            'rpbi_bi2_punzocortantes' => $incineracion ? null : $this->amount(1, 80),
            'rpbi_bi4_no_anatomicos' => $incineracion ? null : $this->amount(1, 90),
            'rpbi_bi5_sangre' => $incineracion ? null : $this->amount(1, 50),
            'total_procesada' => $incineracion ? null : $this->amount(20, 400),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'deleted_at' => null,
        ];
    }

    /** @return list<object> */
    private function manifestDetailPairs(int $total): array
    {
        $query = DB::table('service_details')
            ->join('manifests', function ($join) {
                $join->on('manifests.service_id', '=', 'service_details.service_id')
                    ->whereNull('manifests.deleted_at');
            })
            ->join('services', 'services.id', '=', 'service_details.service_id')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->where('clients.email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->limit($total);

        if ($this->folioFrom !== null && $this->folioTo !== null) {
            $query->whereBetween('services.folio', [$this->folioFrom, $this->folioTo]);
        }

        return $query->get([
            'manifests.id as manifest_id',
            'service_details.id as service_detail_id',
            'service_details.weight',
        ])->all();
    }

    /** @return list<object> */
    private function testManifests(int $total): array
    {
        $query = DB::table('manifests')
            ->join('services', 'services.id', '=', 'manifests.service_id')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->where('clients.email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->whereNull('manifests.deleted_at')
            ->limit($total);

        if ($this->folioFrom !== null && $this->folioTo !== null) {
            $query->whereBetween('services.folio', [$this->folioFrom, $this->folioTo]);
        }

        return $query->get([
            'manifests.id',
            'services.client_id',
        ])->all();
    }

    /** @return list<int> */
    private function ensureRpbiProfiles(): array
    {
        $now = now()->format('Y-m-d H:i:s');

        foreach (self::RPBI_PROFILES as $profile) {
            if (DB::table('rpbi_profiles')->where('code', $profile['code'])->exists()) {
                continue;
            }

            DB::table('rpbi_profiles')->insert([
                'code' => $profile['code'],
                'name' => $profile['name'],
                'description' => $profile['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return array_map('intval', DB::table('rpbi_profiles')->orderBy('id')->pluck('id')->all());
    }

    private function amount(int $min, int $max): string
    {
        return number_format(mt_rand($min * 100, $max * 100) / 100, 2, '.', '');
    }

    private function deleteWhereIn(string $table, string $column, iterable $ids): int
    {
        $deleted = 0;

        foreach (collect($ids)->chunk(1_000) as $chunk) {
            $deleted += DB::table($table)->whereIn($column, $chunk->all())->delete();
        }

        return $deleted;
    }

    private function nullWhereIn(string $table, string $column, iterable $ids): void
    {
        foreach (collect($ids)->chunk(1_000) as $chunk) {
            DB::table($table)->whereIn($column, $chunk->all())->update([$column => null]);
        }
    }

    /** Fecha aleatoria entre N y M días atrás (formato datetime). */
    private function randomDate(int $maxDaysAgo, int $minDaysAgo): string
    {
        return now()
            ->subDays(mt_rand($minDaysAgo, $maxDaysAgo))
            ->subSeconds(mt_rand(0, 86_399))
            ->format('Y-m-d H:i:s');
    }

    /** Elección ponderada reproducible: ['activo' => 60, 'pendiente' => 20, ...]. */
    private function weighted(array $weights): int|string
    {
        $roll = mt_rand(1, (int) array_sum($weights));

        foreach ($weights as $key => $weight) {
            if (($roll -= $weight) <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    /** @param array<string, int> $counts */
    private function printSummary(array $counts, int $seed, float $startedAt): void
    {
        $labels = [
            'users' => 'Usuarios',
            'zones' => 'Zonas',
            'clients' => 'Clientes',
            'contracts' => 'Contratos',
            'contract_rpbi_profiles' => 'Perfiles RPBI de contratos',
            'client_contracts' => 'Asignaciones cliente-contrato',
            'drivers' => 'Choferes',
            'reactors' => 'Reactores',
            'services' => 'Servicios',
            'service_details' => 'Detalles de servicio',
            'manifests' => 'Manifiestos',
            'invoices' => 'Facturas',
            'waste_process_types' => 'Tipos de proceso',
            'waste_processes' => 'Procesos de residuo',
            'waste_process_details' => 'Detalles de proceso',
            'process_certificates' => 'Certificados',
            'process_folios' => 'Folios de proceso',
            'client_configuration_approvals' => 'Aprobaciones de configuración',
        ];

        $rows = [];

        foreach ($counts as $module => $count) {
            $rows[] = [$labels[$module], number_format($count)];
        }

        $rows[] = ['Total', number_format(array_sum($counts))];

        $this->newLine();
        $this->table(['Módulo', 'Registros'], $rows);

        $this->line('Tiempo: '.number_format(microtime(true) - $startedAt, 1).' segundos');
        $this->line('Memoria máxima: '.number_format(memory_get_peak_usage(true) / 1_048_576, 1).' MB');
        $this->line("Semilla: {$seed}");
        $this->line("Lote: {$this->batch}");

        if (array_key_exists('users', $counts)) {
            $this->newLine();
            $this->line('Usuario demo: admin@'.self::EMAIL_DOMAIN);
            $this->line('Contraseña: password');
        }
    }
}
