<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Genera datos masivos de prueba para todos los módulos reales del proyecto:
 * usuarios (con roles), clientes, contratos, zonas y client_contracts.
 *
 * Los datos generados son identificables y borrables con --truncate:
 *   - users / clients ....... correo con dominio @example.test
 *   - contracts / zones ..... nombre con prefijo "[TD] "
 *   - client_contracts ...... notes con prefijo "[test-data]"
 *
 * Estados usados en client_contracts.status (la tabla no tiene catálogo en código):
 *   0 = pendiente, 1 = activo, 2 = completado, 3 = cancelado.
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
        {--module=* : Generar solamente módulos específicos (users, clients, contracts, zones, client_contracts)}';

    protected $description = 'Genera datos masivos de prueba (solo local/testing) para rendimiento, DataTables, filtros y reportes';

    private const EMAIL_DOMAIN = 'example.test';

    private const NAME_PREFIX = '[TD] ';

    private const NOTES_PREFIX = '[test-data]';

    private const MODULES = ['users', 'clients', 'contracts', 'zones', 'client_contracts'];

    /** Cantidades por escala y módulo. */
    private const SCALES = [
        'tiny' => ['users' => 5, 'clients' => 10, 'contracts' => 5, 'zones' => 3, 'client_contracts' => 15],
        'small' => ['users' => 20, 'clients' => 200, 'contracts' => 30, 'zones' => 20, 'client_contracts' => 300],
        'medium' => ['users' => 100, 'clients' => 2_000, 'contracts' => 100, 'zones' => 80, 'client_contracts' => 4_000],
        'large' => ['users' => 500, 'clients' => 15_000, 'contracts' => 300, 'zones' => 200, 'client_contracts' => 30_000],
        'stress' => ['users' => 2_000, 'clients' => 50_000, 'contracts' => 500, 'zones' => 500, 'client_contracts' => 100_000],
    ];

    private string $batch = '';

    private int $chunkSize = 500;

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
                    'clients' => $this->generateClients(self::SCALES[$scale]['clients']),
                    'contracts' => $this->generateContracts(self::SCALES[$scale]['contracts']),
                    'zones' => $this->generateZones(self::SCALES[$scale]['zones']),
                    'client_contracts' => $this->generateClientContracts(self::SCALES[$scale]['client_contracts']),
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

        $userIds = DB::table('users')
            ->where('email', 'like', "%.{$this->batch}@".self::EMAIL_DOMAIN)
            ->pluck('id')
            ->all();

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

        $bar = $this->output->createProgressBar($total);
        $rows = [];

        for ($i = 1; $i <= $total; $i++) {
            $createdAt = $this->randomDate(730, 0);

            $rows[] = [
                'name' => fake()->firstName(),
                'parentarl_surname' => fake()->lastName(),
                'email' => "cliente{$i}.{$this->batch}@".self::EMAIL_DOMAIN,
                'phone' => '81'.str_pad((string) mt_rand(0, 99_999_999), 8, '0', STR_PAD_LEFT),
                'company' => fake()->company(),
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
                'notes' => self::NOTES_PREFIX.(mt_rand(1, 100) <= 40 ? ' '.fake()->sentence() : ''),
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

    // ---------------------------------------------------------------
    // Truncado seguro: borra SOLO datos marcados como de prueba
    // ---------------------------------------------------------------

    private function truncateTestData(): void
    {
        $this->info('Eliminando datos de prueba anteriores...');

        // Orden inverso de dependencias.
        $deleted = DB::table('client_contracts')->where('notes', 'like', self::NOTES_PREFIX.'%')->delete();
        $this->line("  client_contracts: {$deleted}");

        $deleted = DB::table('clients')->where('email', 'like', '%@'.self::EMAIL_DOMAIN)->delete();
        $this->line("  clients: {$deleted}");

        $deleted = DB::table('contracts')->where('name', 'like', self::NAME_PREFIX.'%')->delete();
        $this->line("  contracts: {$deleted}");

        $deleted = DB::table('zones')->where('name', 'like', self::NAME_PREFIX.'%')->delete();
        $this->line("  zones: {$deleted}");

        $testUserIds = DB::table('users')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->pluck('id');

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
            'clients' => 'Clientes',
            'contracts' => 'Contratos',
            'zones' => 'Zonas',
            'client_contracts' => 'Asignaciones cliente-contrato',
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
