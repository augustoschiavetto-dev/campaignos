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
        Schema::create('arquivos_versoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arquivo_id')->constrained('arquivos')->onDelete('cascade');
            $table->integer('versao');
            $table->date('data');
            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('rascunho');
            $table->text('observacao')->nullable();
            $table->text('conteudo_texto')->nullable(); // Para discursos/roteiros digitados
            $table->string('file_path', 255)->nullable(); // Caso tenha arquivo anexado para essa versão
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arquivos_versoes');
    }
};
