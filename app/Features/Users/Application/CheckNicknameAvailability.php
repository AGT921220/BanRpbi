<?php

namespace App\Features\Users\Application;

use App\Models\User;
use Illuminate\Support\Str;

final class CheckNicknameAvailability
{
    /**
     * @return array{available: bool, message: string, nickname: string}
     */
    public function __invoke(?string $nickname, ?int $ignoreUserId = null): array
    {
        $normalized = Str::lower(trim((string) $nickname));

        if ($normalized === '') {
            return [
                'available' => false,
                'message' => 'El nickname es obligatorio.',
                'nickname' => $normalized,
            ];
        }

        if (! preg_match('/^[a-z0-9._-]{3,50}$/', $normalized)) {
            return [
                'available' => false,
                'message' => 'Usa de 3 a 50 caracteres: letras, números, punto, guion o guion bajo.',
                'nickname' => $normalized,
            ];
        }

        $query = User::query()->where('nickname', $normalized);

        if ($ignoreUserId !== null) {
            $query->whereKeyNot($ignoreUserId);
        }

        if ($query->exists()) {
            return [
                'available' => false,
                'message' => 'Este nickname ya está en uso.',
                'nickname' => $normalized,
            ];
        }

        return [
            'available' => true,
            'message' => 'Nickname disponible.',
            'nickname' => $normalized,
        ];
    }
}
