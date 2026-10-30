<?php

namespace App\Features\Manifests\Application;

use App\Models\Manifest;

class FindManifest
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(int $manifestId): array
    {
        $manifest = Manifest::with(['service' => function ($q) {
            $q->with(['serviceDetails' => function ($q) {
                $q->with('rpbiProfile');
            }]);
            $q->select(['id', 'client_id']);
            $q->with(['client' => function ($q) {
                $q->select([
                    'id',
                    'company as name',
                    'nra',
                    'postal_code',
                    'street',
                    'num_ext',
                    'num_int',
                    'colony',
                    'state_id',
                    'city_id',
                    'email',
                    'phone',
                ])->with(['state' => function ($q) {
                    $q->select(['id', 'name']);
                }])
                    ->with(['city' => function ($q) {
                        $q->select(['id', 'name']);
                    }]);
            }]);
        }])->findOrFail($manifestId);

        return [
            'folio' => $manifest->id,
            'client' => $manifest->service->client,
            'driver' => 'TEST',
            'transportista' => config('business.transportista'),
            'vehicle' => [
                'sct' => 'DATO PENDIENTE',
                'plates' => 'DATO PENDIENTE',
                'type' => 'DATO PENDIENTE',
            ],
            'route' => 'DATO PENDIENTE',
            'details' => $manifest->service->serviceDetails->map(function ($detail) {
                $rpbiProfile = $detail->rpbiProfile;

                return [
                    'id' => $rpbiProfile->id,
                    'name' => $rpbiProfile->name,
                    'code' => $rpbiProfile->code,
                    'description' => $rpbiProfile->description,
                ];
            }),
        ];
    }
}
