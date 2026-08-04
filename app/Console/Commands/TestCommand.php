<?php

namespace App\Console\Commands;

use App\Jobs\TestHorizonJob;
use App\Mail\ClientConfigurationSubmitted;
use App\Models\Client;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('app:test-command')]
#[Description('Command description')]
class TestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = Client::first();
        Mail::to(config('app.default_mail'))->send(new ClientConfigurationSubmitted($client));

        //        TestHorizonJob::dispatch();
    }
}
