<?php

namespace App\Features\RpbiProfiles\Domain;

final readonly class RpbiProfilesResponse
{
    /**
     * @var RpbiProfile[]
     */
    private array $data;
    private int $total;

    public function __construct(int $total, RpbiProfile ...$data)
    {
        $this->data = $data;
        $this->total = $total;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return ['total' => $this->total, 'data' => array_map(
            fn(RpbiProfile $profile) => $profile->toArray(),
            $this->getData()
        )];
    }
}
