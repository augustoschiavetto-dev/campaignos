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
        Schema::create('materiais', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('categoria', 50); // santinhos, adesivos, bandeiras, etc.
            $table->string('codigo_interno', 30)->nullable();
            $table->text('descricao')->nullable();
            $table->string('unidade', 20)->default('un'); // un, milheiro, pct, cx
            $table->integer('quantidade_atual')->default(0);
            $table->integer('quantidade_minima')->default(0);
            $table->string('localizacao', 150)->nullable();
            $table->string('estado_conservacao', 30)->default('novo'); // novo, bom, regular, danificado, em_manutencao, inutilizado
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('valor_estimado', 10, 2)->default(0.00);
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiais');
    }
};
