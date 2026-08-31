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

    protected function prepareForValidation(): void
    {
        if ($this->exists('generate_invoice')) {
            $this->merge([
                'generate_invoice' => filter_var(
                    $this->input('generate_invoice'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                ) ?? false,
            ]);
        }
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
            // Preparado para facturación futura; aún no se genera la factura.
            'generate_invoice' => ['sometimes', 'boolean'],
            'invoice_manifest_count' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:generate_invoice,true,1',
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
            'generate_invoice' => 'generar factura',
            'invoice_manifest_count' => 'manifiestos a facturar',
        ];
    }
}
