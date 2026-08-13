<?php

namespace App\Features\Manifests\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Features\Shared\Query\QueryModifierCategory;
use App\Models\Manifest;

class SearchManifestHeaders
{
    private BuilderFilter $builderFilter;
    public function __construct(BuilderFilter $builderFilter)
    {
        $this->builderFilter = $builderFilter;
    }
    public function __invoke(array $filters = []): array
    {
        $query = $this->builderFilter->__invoke(Manifest::query(), $filters, QueryModifierCategory::FILTER);
        $data = $query->get();
        return $this->builderFilter->paginate(
            builder: Manifest::query(),
            modifiers: $filters,
        );
        return
            [
                'data' => $data->toArray(),
                'total' => $data->count(),
                'meta' =>
                [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $data->count(),
                    'filtered' => $data->count(),
                    'total' => $data->count(),
                ],
                'draw' => 1,
                'recordsTotal' => $data->count(),
                'recordsFiltered' => $data->count(),
            ];
        return [];
    }
}
