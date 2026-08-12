<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveClientConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contract_id' => [
                'nullable',
                'integer',
                Rule::exists('contracts', 'id')->whereNull('deleted_at'),
            ],
            'zone_id' => [
                'nullable',
                'integer',
                Rule::exists('zones', 'id')->where('is_active', true),
            ],
            'start_date' => ['nullable', 'date', 'required_with:contract_id'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_with:contract_id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'profile_ids' => ['nullable', 'array'],
            'profile_ids.*' => [
                'integer',
                Rule::exists('rpbi_profiles', 'id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'contract_id' => 'contrato',
            'zone_id' => 'zona de recolección',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'notes' => 'notas',
            'profile_ids' => 'perfiles',
            'profile_ids.*' => 'perfil',
        ];
    }
}
