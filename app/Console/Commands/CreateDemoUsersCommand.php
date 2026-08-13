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
     * @var list<array{name: string, email: string, role: string}>
     */
    private const USERS = [
        [
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'role' => RoleTypes::ADMIN,
        ],
        [
            'name' => 'Director de Ventas',
            'email' => 'director.ventas@director.com',
            'role' => RoleTypes::DIRECTOR_VENTAS,
        ],
        [
            'name' => 'Director General',
            'email' => 'director.general@director.com',
            'role' => RoleTypes::DIRECTOR_GENERAL,
        ],
        [
            'name' => 'Vendedor 1',
            'email' => 'vendedor1@ventas.com',
            'role' => RoleTypes::VENTAS,
        ],
        [
            'name' => 'Vendedor 2',
            'email' => 'vendedor2@ventas.com',
            'role' => RoleTypes::VENTAS,
        ],
        [
            'name' => 'Logística 1',
            'email' => 'logistica1@logistica.com',
            'role' => RoleTypes::LOGISTICA,
        ],
        [
            'name' => 'Chofer 1',
            'email' => 'chofer1@chofer.com',
            'role' => RoleTypes::CHOFER,
        ],
        [
            'name' => 'Administración / Facturación 1',
            'email' => 'facturacion1@facturacion.com',
            'role' => RoleTypes::FACTURACION,
        ],
        [
            'name' => 'Cliente 1',
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
                    ->where('email', $userData['email'])
                    ->first();

                if ($user === null) {
                    $user = User::query()->create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => $password,
                        'email_verified_at' => now(),
                    ]);

                    $user->syncRoles([$role]);
                    $created++;

                    $this->components->info("Creado: {$userData['email']} ({$userData['role']})");

                    continue;
                }

                if ($user->trashed()) {
                    $user->restore();
                }

                if (! $force) {
                    $skipped++;
                    $this->components->warn("Omitido (ya existe): {$userData['email']}. Usa --force para actualizar.");

                    continue;
                }

                $user->forceFill([
                    'name' => $userData['name'],
                    'password' => $password,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                $user->syncRoles([$role]);
                $updated++;

                $this->components->info("Actualizado: {$userData['email']} ({$userData['role']})");
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
                'No fue posible crear los usuarios demo: '.$exception->getMessage()
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

        // Si ya existen ambos, migrar usuarios al rol Admin y eliminar el legado.
        User::role('Super Administrador')->each(function (User $user) use ($adminRole): void {
            $user->removeRole('Super Administrador');
            $user->assignRole($adminRole);
        });

        $legacyRole->delete();
        $this->components->info('Usuarios migrados de "Super Administrador" a "Admin".');
    }
}
