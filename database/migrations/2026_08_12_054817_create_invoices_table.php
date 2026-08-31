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
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');


            $table->integer('client_id')->unsigned()->nullable();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients');

            $table->string('external_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('verification_url')->nullable();
            $table->text('sello_cfdi')->nullable();
            $table->string('sello_sat')->nullable();
            $table->text('cadena_complemento')->nullable();
            $table->string('serie_sat')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('iva_percentaje')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
