<?php

namespace App\Features\Manifests\Domain;

use App\Models\Manifest;
use InvalidArgumentException;

final class ManifestStatusTransitions
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        Manifest::STATUS_PENDING => [
            Manifest::STATUS_COLLECTED,
            Manifest::STATUS_IN_PROCESS,
        ],
        Manifest::STATUS_COLLECTED => [
            Manifest::STATUS_IN_PROCESS,
            Manifest::STATUS_PROCESSED,
        ],
        Manifest::STATUS_IN_PROCESS => [
            Manifest::STATUS_PROCESSED,
        ],
        Manifest::STATUS_PROCESSED => [
            Manifest::STATUS_INVOICED,
        ],
        Manifest::STATUS_INVOICED => [],
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
            "Transición de manifiesto inválida: {$from} → {$to}."
        );
    }

    public static function transition(Manifest $manifest, string $to): void
    {
        $from = filled($manifest->status)
            ? (string) $manifest->status
            : Manifest::STATUS_PENDING;
        self::assertCanTransition($from, $to);

        if ($from === $to) {
            return;
        }

        $manifest->status = $to;
        $manifest->save();
    }
}
