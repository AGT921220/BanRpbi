<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test', function () {
    $this->comment('test');
})->purpose('Display an inspiring quote');


// Artisan::command('invoices:handle', function () {
//     DispatchInvoiceCreationJobs::dispatch()->onQueue('invoices');

//     $this->info('Facturas procesadas correctamente.');
// });



// Schedule::job(new DispatchInvoiceCreationJobs)
//     ->everyMinute();