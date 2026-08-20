<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->decimal('cost', 12, 2)->default(0)->after('frequency');
        });

        Schema::create('contract_rpbi_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('contract_id');
            $table->unsignedInteger('rpbi_profile_id');
            $table->timestamps();

            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->cascadeOnDelete();

            $table->foreign('rpbi_profile_id')
                ->references('id')
                ->on('rpbi_profiles');

            $table->unique(
                ['contract_id', 'rpbi_profile_id'],
                'contract_rpbi_profiles_unique',
            );
        });

        $this->copyProfilesFromClientContracts();

        Schema::dropIfExists('client_contract_profiles');
    }

    public function down(): void
    {
        Schema::create('client_contract_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('client_contract_id');
            $table->unsignedInteger('rpbi_profile_id');
            $table->timestamps();

            $table->foreign('rpbi_profile_id')->references('id')->on('rpbi_profiles');
            $table->foreign('client_contract_id')
                ->references('id')
                ->on('client_contracts')
                ->cascadeOnDelete();

            $table->unique(
                ['client_contract_id', 'rpbi_profile_id'],
                'client_contract_profiles_unique',
            );
        });

        Schema::dropIfExists('contract_rpbi_profiles');

        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropColumn('cost');
        });
    }

    private function copyProfilesFromClientContracts(): void
    {
        if (! Schema::hasTable('client_contract_profiles')) {
            return;
        }

        $now = now();

        $pairs = DB::table('client_contract_profiles')
            ->join(
                'client_contracts',
                'client_contracts.id',
                '=',
                'client_contract_profiles.client_contract_id',
            )
            ->whereNotNull('client_contracts.contract_id')
            ->select(
                'client_contracts.contract_id',
                'client_contract_profiles.rpbi_profile_id',
            )
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            DB::table('contract_rpbi_profiles')->insertOrIgnore([
                'contract_id' => $pair->contract_id,
                'rpbi_profile_id' => $pair->rpbi_profile_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
