<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Requests\Admin\Concerns\ResolvesClientStateCity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreClientRequest extends FormRequest
{
    use ResolvesClientStateCity;

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
            'nra' => ['required', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:13', 'regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/'],
            'street' => ['required', 'string', 'max:255'],
            'num_ext' => ['nullable', 'string', 'max:30'],
            'num_int' => ['nullable', 'string', 'max:30'],
            'postal_code' => ['required', 'string', 'max:10'],
            'colony' => ['nullable', 'string', 'max:255'],
            ...$this->stateCityRules(),
            'maps_url' => ['nullable', 'string', 'max:2048'],
            'maps_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
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
            'nra' => 'NRA',
            'rfc' => 'RFC',
            'street' => 'calle',
            'num_ext' => 'número exterior',
            'num_int' => 'número interior',
            'postal_code' => 'código postal',
            'colony' => 'colonia',
            ...$this->stateCityAttributes(),
            'maps_url' => 'enlace de Google Maps',
            'maps_place_id' => 'lugar de Google Maps',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
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
            ...$this->stateCityMessages(),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('rfc')) {
            $this->merge([
                'rfc' => strtoupper(trim((string) $this->input('rfc'))),
            ]);
        }

        $this->resolveStateCityFromCatalog();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateStateCityCatalog($validator);
        });
    }
}
