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
        Schema::create('solicitacoes_imprensa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos_imprensa')->onDelete('cascade');
            $table->foreignId('jornalista_id')->nullable()->constrained('relacionamentos')->nullOnDelete();
            $table->text('pauta');
            $table->date('data_recebida');
            $table->dateTime('prazo_resposta')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('candidato_porta_voz', 150)->nullable();
            $table->foreignId('evento_relacionado_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->string('status', 30)->default('recebida'); // recebida, em_analise, preparando_resposta, confirmada, respondida, recusada, cancelada
            $table->text('observacoes')->nullable();
            $table->text('resposta_preparada')->nullable();
            $table->text('resultado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_imprensa');
    }
};
