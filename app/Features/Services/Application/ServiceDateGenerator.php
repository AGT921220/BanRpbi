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
        $date = $this->adjustBusinessDay($startDate->copy());

        while ($date->lessThan($endDate)) {
            yield $date->copy();

            $date = $this->adjustBusinessDay(
                $this->getNextDate($date, $frequency)
            );
        }
    }

    private function getNextDate(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeek(),
            'biweekly' => $date->copy()->addWeeks(2),
            'monthly' => $date->copy()->addMonthNoOverflow(),
            default => $date->copy()->addWeek(),
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
