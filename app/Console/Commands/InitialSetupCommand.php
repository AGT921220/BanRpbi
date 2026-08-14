<?php

namespace App\Console\Commands;

use App\Features\Permissions\Constants\RoleTypes;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

#[Signature('initial-setup')]
#[Description('Command description')]
class InitialSetupCommand extends Command
{


    private const USERS = [
        [
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'role' => RoleTypes::ADMIN,
        ]
    ];
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = 'admin';
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

                $this->components->info("Creado: {$userData['email']} ({$userData['role']})");

                continue;
            }

            if ($user->trashed()) {
                $user->restore();
            }

            $user->forceFill([
                'name' => $userData['name'],
                'password' => $password,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            $user->syncRoles([$role]);

            $this->components->info("Actualizado: {$userData['email']} ({$userData['role']})");
        }
    }
}
