<?php

namespace App\Features\Invoices\Jobs;

use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class DispatchInvoiceCreationJobs implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $services = Service::whereDate('service_date', $now)->get();

        foreach ($services as $service) {
            info('Dispatching invoice creation job for service '.$service->id);
            // CreateInvoiceJob::dispatch($service)->onQueue('invoices');
        }
//        info('Invoice creation jobs dispatched');
    }
}
