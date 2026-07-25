<?php

namespace App\Features\Contracts\Application;

use App\Models\Client;
use Illuminate\Support\Collection;

final class ListContractClients
{
    /**
     * @return Collection<int, Client>
     */
    public function __invoke(): Collection
    {
        return Client::query()
            ->orderBy('name')
            ->orderBy('parentarl_surname')
            ->get(['id', 'name', 'parentarl_surname', 'company']);
    }
}
