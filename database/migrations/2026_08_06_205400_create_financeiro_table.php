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
        Schema::create('financeiro_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('categoria', 100);
            $table->decimal('valor', 12, 2);
            $table->date('data_lancamento');
            $table->string('nome_cadastrado', 150);
            $table->string('cpf_cnpj', 20);
            $table->string('meio_pagamento', 50);
            $table->string('comprovante_path', 255)->nullable();
            
            // Novo fluxo de status
            $table->enum('status', ['rascunho', 'pendente_conferencia', 'conferido', 'retificacao_solicitada', 'retificado', 'cancelado'])->default('rascunho');
            
            // Protocolo Interno (substitui recibo oficial automático)
            $table->string('protocolo_interno', 30)->nullable()->unique();
            $table->foreignId('criado_por_id')->constrained('users');
            $table->text('observacoes')->nullable();

            // 1. Campos do Recibo Oficial (Justiça Eleitoral)
            $table->boolean('recibo_oficial_necessario')->default(false);
            $table->enum('recibo_oficial_status', [
                'nao_avaliado', 'necessario', 'dispensado', 'aguardando_emissao', 'emitido', 'conferido', 'divergente', 'cancelado_no_sistema_oficial'
            ])->default('nao_avaliado');
            $table->string('recibo_oficial_numero', 50)->nullable();
            $table->date('recibo_oficial_data_emissao')->nullable();
            $table->string('recibo_oficial_arquivo', 255)->nullable();
            $table->unsignedBigInteger('recibo_oficial_registrado_por')->nullable();
            $table->unsignedBigInteger('recibo_oficial_conferido_por')->nullable();
            $table->timestamp('recibo_oficial_data_conferencia')->nullable();
            $table->text('recibo_oficial_observacao')->nullable();

            // 2. Decisão sobre Exigência de Recibo
            $table->enum('exigencia_decisao', ['necessario', 'dispensado'])->nullable();
            $table->unsignedBigInteger('exigencia_responsavel_id')->nullable();
            $table->timestamp('exigencia_data')->nullable();
            $table->text('exigencia_justificativa')->nullable();
            $table->text('exigencia_orientacao_contabil')->nullable();

            // 3. Retificação Histórica (imutabilidade corretiva)
            $table->decimal('valor_anterior', 12, 2)->nullable();
            $table->decimal('valor_novo', 12, 2)->nullable();
            $table->unsignedBigInteger('retificado_por_id')->nullable();
            $table->timestamp('retificado_em')->nullable();
            $table->text('retificado_motivo')->nullable();
            $table->string('comprovante_path_anterior', 255)->nullable();

            // 4. Conciliação Bancária
            $table->string('conta_bancaria_campanha', 50)->nullable();
            $table->date('data_transacao_bancaria')->nullable();
            $table->string('identificador_bancario', 100)->nullable();
            $table->decimal('valor_bancario', 12, 2)->nullable();
            $table->enum('situacao_conciliacao', ['nao_conciliado', 'conciliado', 'divergente', 'aguardando_documento', 'estornado'])->default('nao_conciliado');
            $table->timestamp('data_conciliacao')->nullable();
            $table->unsignedBigInteger('conciliado_por_id')->nullable();
            $table->text('divergencia_identificada')->nullable();
            $table->text('justificativa_divergencia')->nullable();

            $table->timestamps();

            // Foreign keys opcionais para auditorias financeiras
            $table->foreign('recibo_oficial_registrado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('recibo_oficial_conferido_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('exigencia_responsavel_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('retificado_por_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('conciliado_por_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('financeiro_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lancamento_id')->constrained('financeiro_lancamentos')->onDelete('cascade');
            $table->enum('tipo_documento', [
                'comprovante_bancario', 'registro_interno', 'recibo_oficial_tse', 'documento_doador', 'termo_doacao_estimavel', 'contrato', 'declaracao', 'documento_origem', 'outro_contabil'
            ]);
            $table->string('arquivo_path', 255);
            $table->foreignId('criado_por_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financeiro_documentos');
        Schema::dropIfExists('financeiro_lancamentos');
    }
};
