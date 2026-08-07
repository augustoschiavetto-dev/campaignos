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
        Schema::create('locais_estrategicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('tipo', 50); // comercio, feira, praca, associacao, escola, etc.
            $table->string('endereco', 255)->nullable();
            $table->foreignId('bairro_id')->constrained('bairros')->onDelete('cascade');
            $table->string('contato_responsavel', 150)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('nivel_prioridade', 20)->default('normal'); // alta, normal, baixa
            $table->date('data_ultima_visita')->nullable();
            $table->string('proxima_acao', 255)->nullable();
            $table->string('status', 20)->default('ativo'); // ativo, inativo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locais_estrategicos');
    }
};
