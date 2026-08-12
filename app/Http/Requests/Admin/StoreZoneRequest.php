<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Rules\ValidGeoJsonPolygon;
use Illuminate\Foundation\Http\FormRequest;

final class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::ZONES_CREATE) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $geometry = $this->input('geometry');

        if (is_string($geometry)) {
            $decoded = json_decode($geometry, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'geometry' => $decoded,
                ]);
            }
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
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
            'description' => ['nullable', 'string', 'max:2000'],
            'color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'geometry' => ['required', 'array', new ValidGeoJsonPolygon],
            'geometry.type' => ['required', 'in:Polygon'],
            'geometry.coordinates' => ['required', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'color' => 'color',
            'geometry' => 'geometría',
            'is_active' => 'estado activo',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'geometry.required' => 'Debes dibujar una zona válida en el mapa.',
            'color.regex' => 'El color debe tener el formato hexadecimal #RRGGBB.',
        ];
    }
}
