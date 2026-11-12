<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_processes', function (Blueprint $table) {
            $table->decimal('cl_en_rps', 8, 2)->nullable();
            $table->decimal('co_ppm', 8, 2)->nullable();
            $table->decimal('radiacion_residuos', 8, 2)->nullable();
            $table->decimal('solidos_mixtos', 8, 2)->nullable();
            $table->decimal('rp_solidos_lodos', 8, 2)->nullable();
            $table->decimal('medicamentos_caducos', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('waste_processes', function (Blueprint $table) {
            $table->dropColumn([
                'cl_en_rps',
                'co_ppm',
                'radiacion_residuos',
                'solidos_mixtos',
                'rp_solidos_lodos',
                'medicamentos_caducos',
            ]);
        });
    }
};
