<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Features\Catalogs\Application\ResolveStateCity;
use Illuminate\Validation\Validator;

trait ResolvesClientStateCity
{
    protected function resolveStateCityFromCatalog(): void
    {
        $resolved = app(ResolveStateCity::class)(
            $this->input('state'),
            $this->input('city'),
        );

        $this->merge([
            'state_id' => $resolved['state_id'],
            'city_id' => $resolved['city_id'],
            'state_catalog_found' => $resolved['state_found'],
            'city_catalog_found' => $resolved['city_found'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function stateCityRules(): array
    {
        return [
            'state' => ['nullable', 'string', 'max:255', 'required_with:city'],
            'city' => ['nullable', 'string', 'max:255', 'required_with:state'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function stateCityAttributes(): array
    {
        return [
            'city' => 'municipio',
            'state' => 'estado',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function stateCityMessages(): array
    {
        return [
            'state.required_with' => 'Indica el estado para el municipio seleccionado.',
            'city.required_with' => 'Indica el municipio para el estado seleccionado.',
        ];
    }

    protected function validateStateCityCatalog(Validator $validator): void
    {
        $stateName = trim((string) $this->input('state'));
        $cityName = trim((string) $this->input('city'));

        if ($stateName !== '' && ! $this->input('state_catalog_found')) {
            $validator->errors()->add(
                'state',
                'El estado "'.$stateName.'" no está en el catálogo permitido.',
            );
        }

        if ($cityName !== '' && ! $this->input('city_catalog_found')) {
            $stateLabel = $stateName !== '' ? $stateName : 'el estado seleccionado';

            $validator->errors()->add(
                'city',
                'El municipio "'.$cityName.'" no pertenece al catálogo de '.$stateLabel.'.',
            );
        }
    }
}
