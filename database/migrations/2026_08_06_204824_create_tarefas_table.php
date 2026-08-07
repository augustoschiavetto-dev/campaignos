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
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('descricao')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('data_inicio')->nullable();
            $table->date('prazo')->nullable();
            $table->string('prioridade', 20)->default('normal'); // critica, alta, normal, baixa
            $table->string('status', 20)->default('pendente'); // pendente, em_andamento, aguardando, concluida, cancelada
            $table->json('checklist')->nullable(); // Coleção de itens {titulo: string, concluido: boolean}
            $table->timestamp('data_conclusao')->nullable();
            $table->string('relacionado_type')->nullable(); // Polimórfico (Eventos, Relacionamentos, etc.)
            $table->unsignedBigInteger('relacionado_id')->nullable(); // Polimórfico
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
