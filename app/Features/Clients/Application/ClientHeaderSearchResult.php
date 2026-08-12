<?php

namespace App\Features\Clients\Application;

use Illuminate\Support\Collection;

final readonly class ClientHeaderSearchResult
{
    /**
     * @param  Collection<int, ClientHeader>  $data
     */
    public function __construct(
        public Collection $data,
        public int $total,
        public int $filtered,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {}
}
