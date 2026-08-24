<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;

class CheckNicknameRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return ($user?->can(PermissionTypes::USERS_CREATE) ?? false)
            || ($user?->can(PermissionTypes::USERS_UPDATE) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:50'],
            'ignore_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nickname' => 'nickname',
            'ignore_user_id' => 'usuario',
        ];
    }
}
