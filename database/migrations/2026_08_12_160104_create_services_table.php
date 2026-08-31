<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');

            $table->date('service_date');

            $table->unsignedInteger('zone_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('contract_id');

            $table->string('status')->default('pending');
            $table->integer('folio')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('zone_id')->references('id')->on('zones');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('contract_id')->references('id')->on('contracts');

            $table->integer('invoice_id')->unsigned()->nullable();
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
