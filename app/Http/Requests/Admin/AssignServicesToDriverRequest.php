<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignServicesToDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::COLLECTIONS_ASSIGN) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_date' => ['required', 'date'],
            'driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', Rule::exists('services', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_date' => 'fecha de recolección',
            'driver_id' => 'chofer',
            'service_ids' => 'recolecciones',
            'service_ids.*' => 'recolección',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_ids.required' => 'Selecciona al menos una recolección.',
            'service_ids.min' => 'Selecciona al menos una recolección.',
        ];
    }
}
