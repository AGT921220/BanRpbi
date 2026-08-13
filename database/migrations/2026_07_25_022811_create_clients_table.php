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
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('nra');
            $table->string('parentarl_surname');
            $table->string('email');
            $table->string('phone');
            $table->string('company');
            $table->string('rfc', 13)->nullable()->after('company');
            $table->string('street')->nullable()->after('rfc');
            $table->string('num_ext', 30)->nullable()->after('street');
            $table->string('num_int', 30)->nullable()->after('num_ext');
            $table->string('postal_code', 10)->nullable()->after('num_int');
            $table->string('colony')->nullable()->after('postal_code');
            $table->string('city')->nullable()->after('colony');
            $table->string('state')->nullable()->after('city');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
