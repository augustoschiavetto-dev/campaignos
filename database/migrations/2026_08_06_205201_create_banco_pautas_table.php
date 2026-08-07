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
        Schema::create('banco_pautas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('descricao')->nullable();
            $table->string('origem', 100)->nullable();
            $table->string('tema', 100)->nullable();
            $table->foreignId('contato_relacionado_id')->nullable()->constrained('relacionamentos')->nullOnDelete();
            $table->foreignId('bairro_relacionado_id')->nullable()->constrained('bairros')->nullOnDelete();
            $table->foreignId('demanda_relacionada_id')->nullable()->constrained('demandas_compromissos')->nullOnDelete();
            $table->string('prioridade', 20)->default('normal'); // critica, alta, normal, baixa
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('nova'); // nova, em_avaliacao, aprovada, transformada_conteudo, descartada, arquivada
            $table->date('prazo')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banco_pautas');
    }
};
