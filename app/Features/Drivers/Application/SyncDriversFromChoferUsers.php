<?php

namespace App\Features\Drivers\Application;

use App\Features\Permissions\Constants\RoleTypes;
use App\Models\Driver;
use App\Models\User;

final class SyncDriversFromChoferUsers
{
    public function __invoke(): int
    {
        $created = 0;

        $users = User::role(RoleTypes::CHOFER)->orderBy('id')->get();

        foreach ($users as $user) {
            if (Driver::query()->where('user_id', $user->id)->exists()) {
                continue;
            }

            Driver::query()->create([
                'user_id' => $user->id,
                ...$this->driverDataFromUser($user),
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * @return array{
     *     name: string,
     *     parentarl_surname: string,
     *     maternal_surname: string,
     *     phone: string
     * }
     */
    private function driverDataFromUser(User $user): array
    {
        $parts = preg_split('/\s+/', trim($user->name)) ?: [];

        return [
            'name' => $parts[0] ?? 'Chofer',
            'parentarl_surname' => $parts[1] ?? 'Demo',
            'maternal_surname' => $parts[2] ?? 'Chofer',
            'phone' => '55'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
        ];
    }
}
