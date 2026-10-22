<?php

namespace Tests\Unit\Services;

use App\Features\Services\Domain\ServiceStatusTransitions;
use App\Models\Service;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServiceStatusTransitionsTest extends TestCase
{
    public function test_allows_pending_to_scheduled(): void
    {
        $this->assertTrue(
            ServiceStatusTransitions::canTransition(
                Service::STATUS_PENDING,
                Service::STATUS_SCHEDULED,
            )
        );
    }

    public function test_rejects_collected_to_pending(): void
    {
        $this->assertFalse(
            ServiceStatusTransitions::canTransition(
                Service::STATUS_COLLECTED,
                Service::STATUS_PENDING,
            )
        );
    }

    public function test_assert_throws_on_invalid_transition(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceStatusTransitions::assertCanTransition(
            Service::STATUS_COLLECTED,
            Service::STATUS_SCHEDULED,
        );
    }
}
