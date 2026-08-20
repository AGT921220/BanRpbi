<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::CONTRACTS_CREATE) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('notes') === '') {
            $this->merge(['notes' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:contracts,name'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'frequency' => ['required', 'string', Rule::in(Contract::FREQUENCIES)],
            'cost' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'profile_ids' => ['required', 'array', 'min:1'],
            'profile_ids.*' => ['integer', Rule::exists('rpbi_profiles', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'notes' => 'notas',
            'duration_months' => 'duración en meses',
            'frequency' => 'frecuencia',
            'cost' => 'costo',
            'profile_ids' => 'perfiles RPBI',
            'profile_ids.*' => 'perfil RPBI',
        ];
    }
}
