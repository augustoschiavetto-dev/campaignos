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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->string('tipo', 50); // caminhada, reuniao, entrevista, gravacao, visita, acao_rua, compromisso_interno, compromisso_pessoal_bloqueado
            $table->text('descricao')->nullable();
            $table->dateTime('data_hora_inicio');
            $table->dateTime('data_hora_fim');
            $table->string('endereco', 255)->nullable();
            $table->foreignId('bairro_id')->nullable()->constrained('bairros')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prioridade', 20)->default('importante'); // obrigatoria, importante, opcional
            $table->string('status', 20)->default('solicitado'); // solicitado, em_analise, confirmado, realizado, cancelado, recusado
            $table->integer('tempo_deslocamento_manual')->default(0); // Minutos
            $table->decimal('custo_estimado', 10, 2)->default(0.00);
            $table->decimal('custo_realizado', 10, 2)->default(0.00);
            $table->text('resultado')->nullable();
            $table->json('checklist')->nullable(); // Coleção de itens {titulo: string, concluido: boolean}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
