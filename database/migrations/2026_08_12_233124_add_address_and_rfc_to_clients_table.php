<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('rfc', 13)->nullable()->after('company');
            $table->string('street')->nullable()->after('rfc');
            $table->string('num_ext', 30)->nullable()->after('street');
            $table->string('num_int', 30)->nullable()->after('num_ext');
            $table->string('postal_code', 10)->nullable()->after('num_int');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'rfc',
                'street',
                'num_ext',
                'num_int',
                'postal_code',
            ]);
        });
    }
};
