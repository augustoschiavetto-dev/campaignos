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
        Schema::create('bairros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->onDelete('cascade');
            $table->foreignId('regiao_id')->constrained('regioes')->onDelete('cascade');
            $table->string('nome', 150);
            $table->string('nome_alternativo', 150)->nullable();
            $table->string('prioridade', 20)->default('normal'); // estrategica, alta, normal, baixa
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('populacao_estimada_manual')->default(0);
            $table->integer('meta_contatos')->default(0);
            $table->text('observacoes')->nullable();
            $table->date('data_ultima_acao')->nullable();
            $table->date('data_proxima_acao')->nullable();
            $table->string('status_cobertura', 30)->default('nao_iniciado'); // nao_iniciado, em_mapeamento, em_aproximacao, ativo, consolidado, precisa_retornar, suspenso
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bairros');
    }
};
