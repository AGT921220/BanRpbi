<?php

namespace App\Console\Commands;

use App\Features\Permissions\PermissionHandler;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class CreatePermissionCommand extends Command
{
    protected $signature = 'permissions:create
                            {--guard=web : Guard utilizado por los permisos}';

    protected $description = 'Crea y actualiza los permisos del sistema';

    public function handle(
        PermissionHandler $permissionHandler,
        PermissionRegistrar $permissionRegistrar
    ): int {
        try {
            $guardName = (string) $this->option('guard');

            $permissionRegistrar->forgetCachedPermissions();

            $permissions = $permissionHandler->getAllPermissions();

            $progressBar = $this->output->createProgressBar(
                count($permissions)
            );

            $progressBar->start();

            foreach ($permissions as $permissionName) {
                Permission::findOrCreate(
                    $permissionName,
                    $guardName
                );

                $progressBar->advance();
            }

            $progressBar->finish();

            $this->newLine(2);

            $permissionRegistrar->forgetCachedPermissions();

            $this->components->info(
                count($permissions) . ' permisos procesados correctamente.'
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error(
                'No fue posible crear los permisos: '
                    . $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
