<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;

final class SearchClientHeadersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::CLIENTS_VIEW) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'draw' => ['nullable', 'integer', 'min:0'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:-1', 'max:100'],

            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search.value' => ['nullable', 'string', 'max:150'],
            'search' => ['nullable'],

            'order.0.column' => ['nullable', 'integer', 'min:0'],
            'order.0.dir' => ['nullable', 'in:asc,desc'],

            'order_by' => ['nullable', 'string', 'max:50'],
            'order_direction' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $search = $this->input('search');

            if (
                is_string($search)
                && mb_strlen($search) > 150
            ) {
                $validator->errors()->add(
                    'search',
                    'El campo search no debe ser mayor que 150 caracteres.',
                );
            }

            if (
                $search !== null
                && ! is_string($search)
                && ! is_array($search)
            ) {
                $validator->errors()->add(
                    'search',
                    'El campo search debe ser una cadena o un arreglo.',
                );
            }
        });
    }
}
