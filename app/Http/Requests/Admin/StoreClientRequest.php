<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;

final class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::CLIENTS_CREATE) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parentarl_surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
            'phone' => ['required', 'string', 'max:30'],
            'company' => ['required', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:13', 'regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/'],
            'street' => ['required', 'string', 'max:255'],
            'num_ext' => ['nullable', 'string', 'max:30'],
            'num_int' => ['nullable', 'string', 'max:30'],
            'postal_code' => ['required', 'string', 'max:10'],
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
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'company' => 'empresa',
            'rfc' => 'RFC',
            'street' => 'calle',
            'num_ext' => 'número exterior',
            'num_int' => 'número interior',
            'postal_code' => 'código postal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'El correo electrónico ya está registrado.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('rfc')) {
            $this->merge([
                'rfc' => strtoupper(trim((string) $this->input('rfc'))),
            ]);
        }
    }
}
