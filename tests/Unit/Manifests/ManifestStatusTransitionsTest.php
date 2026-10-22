<?php

namespace Tests\Unit\Manifests;

use App\Features\Manifests\Domain\ManifestStatusTransitions;
use App\Models\Manifest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ManifestStatusTransitionsTest extends TestCase
{
    public function test_allows_pending_to_collected(): void
    {
        $this->assertTrue(
            ManifestStatusTransitions::canTransition(
                Manifest::STATUS_PENDING,
                Manifest::STATUS_COLLECTED,
            )
        );
    }

    public function test_allows_processed_to_invoiced(): void
    {
        $this->assertTrue(
            ManifestStatusTransitions::canTransition(
                Manifest::STATUS_PROCESSED,
                Manifest::STATUS_INVOICED,
            )
        );
    }

    public function test_rejects_invoiced_regression(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ManifestStatusTransitions::assertCanTransition(
            Manifest::STATUS_INVOICED,
            Manifest::STATUS_PENDING,
        );
    }
}
