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
        Schema::create('timeline', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_evento', 50); // contato.criado, tarefa.concluida, despesa.lancada, etc.
            $table->string('titulo', 150); // Mensagem rápida legível
            $table->text('descricao')->nullable(); // Detalhes adicionais
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('relacionado_type')->nullable(); // Polimórfico
            $table->unsignedBigInteger('relacionado_id')->nullable(); // Polimórfico
            $table->timestamps(); // criado_em será a data/hora do evento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline');
    }
};
