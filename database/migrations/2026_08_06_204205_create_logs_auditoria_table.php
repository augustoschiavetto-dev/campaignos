<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acao', 50); // login, login_falha, logout, exclusao, alteracao, etc.
            $table->string('tabela', 50)->nullable();
            $table->bigInteger('registro_id')->nullable();
            $table->text('valor_anterior')->nullable(); // JSON com o valor anterior
            $table->text('valor_novo')->nullable(); // JSON com o novo valor
            $table->string('ip_origem', 45)->nullable();
            $table->text('navegador')->nullable(); // User Agent completo ou parseado
            $table->string('dispositivo', 100)->nullable(); // Mobile, Desktop, Tablet, etc.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
