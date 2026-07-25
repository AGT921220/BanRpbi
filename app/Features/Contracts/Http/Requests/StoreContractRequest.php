<?php

namespace App\Features\Contracts\Http\Requests;

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
        if ($this->input('collection_frequency') === '') {
            $this->merge(['collection_frequency' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'folio' => ['required', 'string', 'max:50', 'unique:contracts,folio'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'string', Rule::in(Contract::STATUSES)],
            'collection_frequency' => ['nullable', 'string', Rule::in(Contract::FREQUENCIES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'folio' => 'folio',
            'client_id' => 'cliente',
            'name' => 'nombre',
            'starts_at' => 'fecha de inicio',
            'ends_at' => 'fecha de finalización',
            'status' => 'estado',
            'collection_frequency' => 'frecuencia de recolección',
            'notes' => 'notas',
        ];
    }
}
