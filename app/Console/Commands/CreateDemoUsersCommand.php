<?php

namespace App\Console\Commands;

use App\Features\Permissions\Constants\RoleTypes;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class CreateDemoUsersCommand extends Command
{
    protected $signature = 'users:create-demo
                            {--force : Actualiza usuarios existentes con la contraseña y rol definidos}';

    protected $description = 'Crea el Admin y usuarios de demostración por rol (contraseña: admin)';

    /**
     * @var list<array{name: string, nickname: string, email: string, role: string}>
     */
    private const USERS = [
        [
            'name' => 'Administrador',
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'role' => RoleTypes::ADMIN,
        ],
        [
            'name' => 'Director de Ventas',
            'nickname' => 'director.ventas',
            'email' => 'director.ventas@director.com',
            'role' => RoleTypes::DIRECTOR_VENTAS,
        ],
        [
            'name' => 'Director General',
            'nickname' => 'director.general',
            'email' => 'director.general@director.com',
            'role' => RoleTypes::DIRECTOR_GENERAL,
        ],
        [
            'name' => 'Vendedor 1',
            'nickname' => 'vendedor1',
            'email' => 'vendedor1@ventas.com',
            'role' => RoleTypes::VENTAS,
        ],
        [
            'name' => 'Vendedor 2',
            'nickname' => 'vendedor2',
            'email' => 'vendedor2@ventas.com',
            'role' => RoleTypes::VENTAS,
        ],
        [
            'name' => 'Logística 1',
            'nickname' => 'logistica1',
            'email' => 'logistica1@logistica.com',
            'role' => RoleTypes::LOGISTICA,
        ],
        [
            'name' => 'Chofer 1',
            'nickname' => 'chofer1',
            'email' => 'chofer1@chofer.com',
            'role' => RoleTypes::CHOFER,
        ],
        [
            'name' => 'Chofer 2',
            'nickname' => 'chofer2',
            'email' => 'chofer2@chofer.com',
            'role' => RoleTypes::CHOFER,
        ],
        [
            'name' => 'Chofer 3',
            'nickname' => 'chofer3',
            'email' => 'chofer3@chofer.com',
            'role' => RoleTypes::CHOFER,
        ],

        [
            'name' => 'Administración / Facturación 1',
            'nickname' => 'facturacion1',
            'email' => 'facturacion1@facturacion.com',
            'role' => RoleTypes::FACTURACION,
        ],
        [
            'name' => 'Cliente 1',
            'nickname' => 'cliente1',
            'email' => 'cliente1@cliente.com',
            'role' => RoleTypes::CLIENTE,
        ],
    ];

    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        try {
            $permissionRegistrar->forgetCachedPermissions();

            $this->renameLegacyAdminRole();

            $password = 'admin';
            $force = (bool) $this->option('force');
            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach (self::USERS as $userData) {
                $role = Role::findOrCreate($userData['role'], 'web');

                $user = User::query()
                    ->withTrashed()
                    ->where(function ($query) use ($userData): void {
                        $query->where('email', $userData['email'])
                            ->orWhere('nickname', $userData['nickname']);
                    })
                    ->first();

                if ($user === null) {
                    $user = User::query()->create([
                        'name' => $userData['name'],
                        'nickname' => $userData['nickname'],
                        'email' => $userData['email'],
                        'password' => $password,
                        'email_verified_at' => now(),
                    ]);

                    $user->syncRoles([$role]);
                    $created++;

                    $this->components->info("Creado: {$userData['nickname']} ({$userData['role']})");

                    continue;
                }

                if ($user->trashed()) {
                    $user->restore();
                }

                if (! $force) {
                    $skipped++;
                    $this->components->warn("Omitido (ya existe): {$userData['nickname']}. Usa --force para actualizar.");

                    continue;
                }

                $user->forceFill([
                    'name' => $userData['name'],
                    'nickname' => $userData['nickname'],
                    'email' => $userData['email'],
                    'password' => $password,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                $user->syncRoles([$role]);
                $updated++;

                $this->components->info("Actualizado: {$userData['nickname']} ({$userData['role']})");
            }

            $permissionRegistrar->forgetCachedPermissions();

            $this->newLine();
            $this->components->info(
                "Usuarios demo listos. Creados: {$created}, actualizados: {$updated}, omitidos: {$skipped}."
            );
            $this->components->info('Contraseña para todos: admin');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error(
                'No fue posible crear los usuarios demo: ' . $exception->getMessage()
            );

            return self::FAILURE;
        }
    }

    private function renameLegacyAdminRole(): void
    {
        $legacyRole = Role::query()
            ->where('name', 'Super Administrador')
            ->where('guard_name', 'web')
            ->first();

        if ($legacyRole === null) {
            Role::findOrCreate('Admin', 'web');

            return;
        }

        $adminRole = Role::query()
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($adminRole === null) {
            $legacyRole->name = 'Admin';
            $legacyRole->save();
            $this->components->info('Rol "Super Administrador" renombrado a "Admin".');

            return;
        }

        User::role('Super Administrador')->each(function (User $user) use ($adminRole): void {
            $user->removeRole('Super Administrador');
            $user->assignRole($adminRole);
        });

        $legacyRole->delete();
        $this->components->info('Usuarios migrados de "Super Administrador" a "Admin".');
    }
}
