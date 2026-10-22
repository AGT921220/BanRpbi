<?php

namespace App\Features\Services\Domain;

use App\Models\Service;
use InvalidArgumentException;

final class ServiceStatusTransitions
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        Service::STATUS_PENDING => [
            Service::STATUS_SCHEDULED,
            Service::STATUS_COLLECTED,
        ],
        Service::STATUS_SCHEDULED => [
            Service::STATUS_COLLECTED,
            Service::STATUS_PENDING,
        ],
        Service::STATUS_COLLECTED => [
            Service::STATUS_IN_WAREHOUSE,
        ],
        Service::STATUS_IN_WAREHOUSE => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::ALLOWED[$from] ?? [], true);
    }

    public static function assertCanTransition(string $from, string $to): void
    {
        if (self::canTransition($from, $to)) {
            return;
        }

        throw new InvalidArgumentException(
            "Transición de recolección inválida: {$from} → {$to}."
        );
    }

    /**
     * @return list<string>
     */
    public static function allowedFrom(string $from): array
    {
        return self::ALLOWED[$from] ?? [];
    }
}
