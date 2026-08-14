<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATE_NAME = 'Baja California';

    /**
     * @var list<string>
     */
    private const CITIES = [
        'Ensenada',
        'Mexicali',
        'Tecate',
        'Tijuana',
        'Playas de Rosarito',
        'San Quintín',
        'San Felipe',
    ];

    public function up(): void
    {
        Schema::create('states', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states');
            $table->unique(['state_id', 'name']);
            $table->timestamps();
        });

        $this->seedBajaCaliforniaCatalog();
        $this->addClientLocationForeignKeys();
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['state_id']);
            $table->dropColumn(['city_id', 'state_id']);
        });

        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
    }

    private function seedBajaCaliforniaCatalog(): void
    {
        $now = now();

        $stateId = DB::table('states')
            ->where('name', self::STATE_NAME)
            ->value('id');

        if ($stateId === null) {
            $stateId = DB::table('states')->insertGetId([
                'name' => self::STATE_NAME,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::CITIES as $cityName) {
            $exists = DB::table('cities')
                ->where('state_id', $stateId)
                ->where('name', $cityName)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('cities')->insert([
                'name' => $cityName,
                'state_id' => $stateId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function addClientLocationForeignKeys(): void
    {
        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('clients', 'city') ? 'city' : null,
            Schema::hasColumn('clients', 'state') ? 'state' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('clients', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }

        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedInteger('state_id')->nullable()->after('colony');
            $table->unsignedInteger('city_id')->nullable()->after('state_id');
            $table->foreign('state_id')->references('id')->on('states');
            $table->foreign('city_id')->references('id')->on('cities');
        });
    }
};
