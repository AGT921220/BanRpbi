<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TestHorizonJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Log::info('Job procesado correctamente por Horizon.');
    }
}