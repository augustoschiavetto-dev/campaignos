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
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('categoria', 50); // identidade_visual, logo, foto, video, roteiro, discurso, release, documento, material_grafico, etc.
            $table->text('descricao')->nullable();
            $table->string('path_ou_link', 255);
            $table->boolean('is_link_externo')->default(false);
            
            // Associação polimórfica opcional
            $table->string('relacionado_type', 100)->nullable();
            $table->unsignedBigInteger('relacionado_id')->nullable();
            
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tags', 255)->nullable();
            $table->date('data');
            $table->integer('versao')->default(1);
            $table->string('status', 30)->default('ativo'); // ativo, obsoleto, arquivado
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arquivos');
    }
};
