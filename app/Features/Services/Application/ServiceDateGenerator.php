<?php

namespace App\Features\Services\Application;

use Generator;
use Illuminate\Support\Carbon;

class ServiceDateGenerator
{
    public function __invoke(
        Carbon $startDate,
        Carbon $endDate,
        string $frequency
    ): Generator {
        for (
            $date = $startDate->copy();
            $date->lessThan($endDate);
            $date = $this->getNextDate($date, $frequency)
        ) {
            yield $this->adjustBusinessDay($date);
        }
    }
    private function getNextDate(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeek(),
            'biweekly' => $date->copy()->addWeeks(2),
            'monthly' => $date->copy()->addMonthNoOverflow()
        };
    }

    private function adjustBusinessDay(Carbon $date): Carbon
    {
        $date = $date->copy();

        if ($date->isSaturday()) {
            return $date->subDay();
        }

        if ($date->isSunday()) {
            return $date->addDay();
        }

        return $date;
    }
}
