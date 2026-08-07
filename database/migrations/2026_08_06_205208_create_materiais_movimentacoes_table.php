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
        Schema::create('materiais_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materiais')->onDelete('cascade');
            $table->string('tipo_movimentacao', 30); // entrada, saida, entrega, retirada, devolucao, perda, descarte, ajuste, manutencao
            $table->integer('quantidade');
            $table->integer('quantidade_anterior');
            $table->integer('quantidade_nova');
            $table->dateTime('data_hora');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Destinatário pode ser usuário interno ou contato do CRM (apoiador/voluntário)
            $table->unsignedBigInteger('destinatario_id')->nullable();
            $table->string('destinatario_type', 100)->nullable(); // User ou Relacionamento

            $table->foreignId('evento_relacionado_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->foreignId('bairro_relacionado_id')->nullable()->constrained('bairros')->nullOnDelete();
            $table->text('observacao')->nullable();
            $table->string('comprovante_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiais_movimentacoes');
    }
};
