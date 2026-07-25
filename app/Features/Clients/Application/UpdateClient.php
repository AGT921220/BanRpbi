<?php

namespace App\Features\Clients\Application;

use App\Models\Client;

final class UpdateClient
{
    /**
     * @param  array{
     *     name: string,
     *     parentarl_surname: string,
     *     email: string,
     *     phone: string,
     *     company: string
     * }  $data
     */
    public function __invoke(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->refresh();
    }
}
