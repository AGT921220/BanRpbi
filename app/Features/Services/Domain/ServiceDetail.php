<?php

namespace App\Features\Services\Domain;

use App\Features\RpbiProfiles\Domain\RpbiProfile;

final readonly class ServiceDetail
{
    public function __construct(
        private int $id,
        private ?string $weight,
        private ?RpbiProfile $rpbiProfile,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function getRpbiProfile(): ?RpbiProfile
    {
        return $this->rpbiProfile;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'weight' => $this->getWeight(),
            'rpbi_profile' => $this->rpbiProfile?->toArray(),
        ];
    }
}
