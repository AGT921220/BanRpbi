<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contract_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('client_contract_id');
            $table->foreignId('rpbi_profile_id')->constrained('rpbi_profiles')->cascadeOnDelete();
            $table->timestamps();

            $table->foreign('client_contract_id')
                ->references('id')
                ->on('client_contracts')
                ->cascadeOnDelete();

            $table->unique(
                ['client_contract_id', 'rpbi_profile_id'],
                'client_contract_profiles_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contract_profiles');
    }
};
