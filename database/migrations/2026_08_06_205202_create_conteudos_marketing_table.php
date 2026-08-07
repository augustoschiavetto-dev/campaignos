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
        Schema::create('conteudos_marketing', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->string('tema', 100)->nullable();
            $table->text('objetivo')->nullable();
            $table->string('tipo', 50); // reel, story, carrossel, video_longo, etc.
            $table->string('publico_alvo', 150)->nullable();
            $table->foreignId('bairro_relacionado_id')->nullable()->constrained('bairros')->nullOnDelete();
            $table->foreignId('evento_relacionado_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->foreignId('demanda_relacionada_id')->nullable()->constrained('demandas_compromissos')->nullOnDelete();
            $table->foreignId('campanha_tematica_id')->nullable()->constrained('campanhas_tematicas')->nullOnDelete();
            $table->foreignId('pauta_origem_id')->nullable()->constrained('banco_pautas')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->date('data_criacao');
            $table->date('prazo')->nullable();
            $table->date('data_prevista_gravacao')->nullable();
            $table->date('data_prevista_publicacao')->nullable();
            
            $table->text('roteiro_texto')->nullable();
            $table->string('chamada_principal', 255)->nullable();
            $table->text('observacoes_internas')->nullable();
            $table->string('status', 30)->default('ideia'); // ideia, pauta, roteiro, aguardando gravação, etc.
            $table->string('prioridade', 20)->default('normal'); // critica, alta, normal, baixa
            $table->string('link_arquivos', 255)->nullable();
            $table->string('link_publicado', 255)->nullable();
            $table->date('data_real_publicacao')->nullable();
            $table->text('canais')->nullable(); // Guardado como JSON (Instagram, Facebook, etc.)

            // Fluxo de aprovação
            $table->foreignId('aprovado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('data_aprovacao')->nullable();
            $table->text('texto_approved')->nullable(); // Campo interno de texto aprovado
            
            // Revisão jurídica
            $table->boolean('revisao_juridica_necessaria')->default(false);
            $table->string('revisao_juridica_status', 30)->default('nao_solicitado'); // nao_solicitado, pendente, aprovado, ajustes_necessarios
            $table->text('revisao_juridica_observacao')->nullable();

            // Métricas Manuais
            $table->integer('metricas_visualizacoes')->default(0);
            $table->integer('metricas_alcance')->default(0);
            $table->integer('metricas_curtidas')->default(0);
            $table->integer('metricas_comentarios')->default(0);
            $table->integer('metricas_compartilhamentos')->default(0);
            $table->integer('metricas_salvamentos')->default(0);
            $table->integer('metricas_cliques')->default(0);
            $table->integer('metricas_mensagens')->default(0);
            $table->integer('metricas_contatos_gerados')->default(0);
            $table->text('metricas_desempenho_obs')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conteudos_marketing');
    }
};
