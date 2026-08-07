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
        Schema::create('entrevistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos_imprensa')->onDelete('cascade');
            $table->foreignId('jornalista_id')->nullable()->constrained('relacionamentos')->nullOnDelete();
            $table->text('pauta');
            $table->date('data');
            $table->string('horario', 10);
            $table->string('local_link', 255)->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('porta_voz', 150)->nullable();
            $table->string('status', 30)->default('agendada'); // agendada, realizada, cancelada

            // Briefing protegido (Requisito 8)
            $table->text('briefing')->nullable();
            $table->text('perguntas_provaveis')->nullable();
            $table->text('pontos_atencao')->nullable();
            $table->text('respostas_sugeridas')->nullable();
            $table->text('assuntos_evitar')->nullable();
            $table->text('compromissos_relacionados')->nullable();

            $table->text('resultado')->nullable();
            $table->string('link_publicado', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrevistas');
    }
};
