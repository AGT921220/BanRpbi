<?php

namespace App\Rules;

use App\Features\Permissions\Constants\RoleTypes;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UserIsChofer implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isChofer = User::query()
            ->whereKey($value)
            ->whereHas('roles', function ($query): void {
                $query->where('name', RoleTypes::CHOFER);
            })
            ->exists();

        if (! $isChofer) {
            $fail('El usuario seleccionado debe tener el rol de chofer.');
        }
    }
}
