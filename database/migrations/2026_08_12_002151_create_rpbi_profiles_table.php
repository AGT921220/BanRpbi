<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<array{code: string, name: string, description: string}>
     */
    private const PROFILES = [
        [
            'code' => 'BI1',
            'name' => 'Cultivos y cepas',
            'description' => 'Cultivos, cepas y material utilizado para contener, transferir o manipular agentes biológico-infecciosos.',
        ],
        [
            'code' => 'BI2',
            'name' => 'Objetos punzocortantes',
            'description' => 'Objetos capaces de cortar o perforar que hayan estado en contacto con material biológico.',
        ],
        [
            'code' => 'BI3',
            'name' => 'Residuos patológicos',
            'description' => 'Tejidos, órganos, partes anatómicas y determinadas muestras biológicas.',
        ],
        [
            'code' => 'BI4',
            'name' => 'Residuos no anatómicos',
            'description' => 'Material desechable contaminado o saturado con sangre, secreciones u otros fluidos biológico-infecciosos.',
        ],
        [
            'code' => 'BI5',
            'name' => 'Sangre',
            'description' => 'Sangre humana y sus componentes en forma líquida.',
        ],
    ];

    public function up(): void
    {
        Schema::create('rpbi_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->string('description');
            $table->timestamps();
        });

        $now = now();

        foreach (self::PROFILES as $profile) {
            $exists = DB::table('rpbi_profiles')
                ->where('code', $profile['code'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('rpbi_profiles')->insert([
                'code' => $profile['code'],
                'name' => $profile['name'],
                'description' => $profile['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rpbi_profiles');
    }
};
