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
     *     company: string,
     *     nra: string,
     *     rfc: string,
     *     street: string,
     *     num_ext?: string|null,
     *     num_int?: string|null,
     *     postal_code: string,
     *     colony?: string|null,
     *     city?: string|null,
     *     state?: string|null,
     *     maps_url?: string|null,
     *     maps_place_id?: string|null,
     *     latitude?: float|null,
     *     longitude?: float|null
     * }  $data
     */
    public function __invoke(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->refresh();
    }
}
