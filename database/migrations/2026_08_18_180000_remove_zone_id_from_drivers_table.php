<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table): void {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table): void {
            $table->unsignedInteger('zone_id')->nullable()->after('phone');
            $table->foreign('zone_id')->references('id')->on('zones');
        });
    }
};
