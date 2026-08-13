<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->text('maps_url')->nullable()->after('postal_code');
            $table->string('maps_place_id')->nullable()->after('maps_url');
            $table->decimal('latitude', 10, 7)->nullable()->after('maps_place_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'maps_url',
                'maps_place_id',
                'latitude',
                'longitude',
            ]);
        });
    }
};
