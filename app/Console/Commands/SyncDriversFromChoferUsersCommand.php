<?php

namespace App\Console\Commands;

use App\Features\Drivers\Application\SyncDriversFromChoferUsers;
use Illuminate\Console\Command;

class SyncDriversFromChoferUsersCommand extends Command
{
    protected $signature = 'drivers:sync-from-chofer-users';

    protected $description = 'Crea registros de chofer para usuarios con rol Chofer que aún no tengan uno';

    public function handle(SyncDriversFromChoferUsers $syncDriversFromChoferUsers): int
    {
        $created = $syncDriversFromChoferUsers();

        $this->components->info(
            $created === 0
                ? 'No había usuarios Chofer pendientes de vincular.'
                : "{$created} chofer(es) creado(s) y vinculado(s) a su usuario."
        );

        return self::SUCCESS;
    }
}
