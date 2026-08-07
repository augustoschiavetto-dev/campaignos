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
        Schema::create('demandas_compromissos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('descricao');
            $table->string('tipo', 50); // demanda, reuniao, visita, problema_bairro, indacao, ajuda, promessa, compromisso_campanha, compromisso_contato, oportunidade, problema_operacional, outro
            $table->foreignId('contato_relacionado_id')->nullable()->constrained('relacionamentos')->nullOnDelete();
            $table->foreignId('lideranca_relacionada_id')->nullable()->constrained('relacionamentos')->nullOnDelete();
            $table->foreignId('bairro_relacionado_id')->nullable()->constrained('bairros')->nullOnDelete();
            $table->foreignId('evento_relacionado_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->foreignId('responsavel_interno_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origem', 100)->nullable();
            $table->date('data_registro');
            $table->date('prazo')->nullable();
            $table->string('prioridade', 20)->default('normal'); // critica, alta, normal, baixa
            $table->string('status', 20)->default('novo'); // novo, em_analise, aprovado, em_andamento, aguardando_terceiro, concluido, cancelado, nao_atendido
            $table->string('proxima_acao', 255)->nullable();
            $table->date('data_proxima_acao')->nullable();
            $table->text('resultado')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->text('observacoes_publicas')->nullable();
            $table->text('observacoes_internas')->nullable(); // restrito a perfis autorizados
            
            // Fluxo de aprovação de compromisso
            $table->foreignId('aprovado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('data_aprovacao')->nullable();
            $table->text('texto_anterior')->nullable();
            $table->text('texto_aprovado')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandas_compromissos');
    }
};
