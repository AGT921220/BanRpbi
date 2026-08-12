<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_configuration_approvals', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('client_contract_id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
//            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_name');
            $table->timestamp('approved_at');
            $table->timestamps();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();

            $table->foreign('client_contract_id')
                ->references('id')
                ->on('client_contracts')
                ->cascadeOnDelete();

            $table->unique(['client_contract_id', 'role_name'], 'cca_contract_role_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_configuration_approvals');
    }
};
