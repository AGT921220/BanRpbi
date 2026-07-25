<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UpdateBulkPasswordsCommand extends Command
{
    protected $signature = 'users:update-bulk-passwords
                            {--force : No pedir confirmación}';

    protected $description = 'Actualiza la contraseña de todos los usuarios a "admin" (solo local)';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Este comando solo puede ejecutarse en local.');

            return self::FAILURE;
        }

        if (
            ! $this->option('force')
            && ! $this->confirm('¿Actualizar la contraseña de TODOS los usuarios a "admin"?')
        ) {
            return self::SUCCESS;
        }

        try {
            $updated = DB::table('users')->update([
                'password' => Hash::make('admin'),
                'updated_at' => now(),
            ]);

            $this->components->info("Contraseñas actualizadas: {$updated}. Nueva contraseña: admin");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error(
                'No fue posible actualizar las contraseñas: '.$exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
