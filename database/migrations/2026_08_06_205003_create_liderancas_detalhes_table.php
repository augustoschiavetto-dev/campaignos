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
        Schema::create('liderancas_detalhes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relacionamento_id')->constrained('relacionamentos')->onDelete('cascade');
            $table->string('area_influencia', 255)->nullable();
            
            // Campos de Avaliação Manual de Votos Estimados
            $table->integer('votos_estimados')->default(0);
            $table->date('data_estimativa')->nullable();
            $table->foreignId('responsavel_estimativa_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('justificativa')->nullable();
            $table->string('nivel_confianca', 20)->nullable(); // alto, medio, baixo
            $table->text('observacoes_influencia')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liderancas_detalhes');
    }
};
