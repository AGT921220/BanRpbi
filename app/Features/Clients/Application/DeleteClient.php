<?php

namespace App\Features\Clients\Application;

use App\Models\Client;

final class DeleteClient
{
    public function __invoke(Client $client): void
    {
        $client->delete();
    }
}
