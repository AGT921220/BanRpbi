<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::USERS_CREATE) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nickname')) {
            $this->merge([
                'nickname' => Str::lower(trim((string) $this->input('nickname'))),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nickname' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9._-]+$/',
                'unique:users,nickname',
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'nickname' => 'nickname',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'roles' => 'roles',
            'permissions' => 'permisos',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nickname.unique' => 'Este nickname ya está en uso.',
            'nickname.regex' => 'El nickname solo puede contener letras minúsculas, números, punto, guion o guion bajo.',
        ];
    }
}
