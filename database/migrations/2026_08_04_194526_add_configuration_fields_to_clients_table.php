<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('configuration_status')
                ->default(Client::STATUS_CONFIGURATION_PENDING)
                ->after('company');


            $table->unsignedInteger('zone_id')->nullable();
            $table->foreign('zone_id')->references('id')->on('zones');


            $table->timestamp('configuration_submitted_at')
                ->nullable()
                ->after('zone_id');

            $table->timestamp('configuration_reviewed_at')
                ->nullable()
                ->after('configuration_submitted_at');

            $table->text('configuration_rejection_reason')
                ->nullable()
                ->after('configuration_reviewed_at');

            $table->index('configuration_status');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('zone_id');
            $table->dropIndex(['configuration_status']);
            $table->dropColumn([
                'configuration_status',
                'configuration_submitted_at',
                'configuration_reviewed_at',
                'configuration_rejection_reason',
            ]);
        });
    }
};
