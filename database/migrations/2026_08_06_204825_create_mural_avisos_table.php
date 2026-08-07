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
        Schema::create('mural_avisos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('mensagem');
            $table->foreignId('autor_id')->constrained('users')->onDelete('cascade');
            $table->string('prioridade', 20)->default('informativo'); // critico, atencao, informativo
            $table->date('data_inicio');
            $table->date('data_expiracao')->nullable();
            $table->boolean('fixado')->default(false);
            $table->string('anexo_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mural_avisos');
    }
};
