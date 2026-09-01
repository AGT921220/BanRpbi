<?php

namespace Tests\Unit\Services;

use App\Features\Services\Application\ServiceDateGenerator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceDateGeneratorTest extends TestCase
{
    #[Test]
    public function test_generates_only_weekday_dates(): void
    {
        $generator = new ServiceDateGenerator;

        $dates = iterator_to_array($generator(
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-10-01'),
            'weekly',
        ));

        $this->assertNotEmpty($dates);

        foreach ($dates as $date) {
            $this->assertFalse($date->isSaturday());
            $this->assertFalse($date->isSunday());
        }
    }

    #[Test]
    public function test_moves_weekend_start_date_to_monday(): void
    {
        $generator = new ServiceDateGenerator;

        $dates = iterator_to_array($generator(
            Carbon::parse('2026-09-06'),
            Carbon::parse('2026-09-20'),
            'weekly',
        ));

        $this->assertSame('2026-09-07', $dates[0]->toDateString());
    }
}
