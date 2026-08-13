<?php

namespace App\Features\Clients\Application;

use App\Models\Client;

final class CreateClient
{
    /**
     * @param  array{
     *     name: string,
     *     parentarl_surname: string,
     *     email: string,
     *     phone: string,
     *     company: string,
     *     rfc: string,
     *     street: string,
     *     num_ext?: string|null,
     *     num_int?: string|null,
     *     postal_code: string
     * }  $data
     */
    public function __invoke(array $data): Client
    {
        return Client::query()->create($data);
    }
}
