<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Rules\UserIsChofer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::DRIVERS_CREATE) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parentarl_surname' => ['required', 'string', 'max:255'],
            'maternal_surname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'zone_id' => ['required', 'integer', Rule::exists('zones', 'id')],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('drivers', 'user_id'),
                new UserIsChofer,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'parentarl_surname' => 'apellido paterno',
            'maternal_surname' => 'apellido materno',
            'phone' => 'teléfono',
            'zone_id' => 'zona',
            'user_id' => 'usuario',
        ];
    }
}
