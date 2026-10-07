<?php

use App\Features\Manifests\Jobs\CreateDailyManifestsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test', function () {
    $this->comment('test');
})->purpose('Display an inspiring quote');


Artisan::command('invoices:handle', function () {
    CreateDailyManifestsJob::dispatch();
    info('Se envía a crear manifiestos');
    $this->info('Facturas procesadas correctamente.');
});
Schedule::command('invoices:handle')
//->everyMinute();
->dailyAt('01:00');


// Schedule::job(new DispatchInvoiceCreationJobs)
//     ->everyMinute();