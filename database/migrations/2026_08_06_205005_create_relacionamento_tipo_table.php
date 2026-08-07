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
        Schema::create('relacionamento_tipo', function (Blueprint $table) {
            $table->foreignId('relacionamento_id')->constrained('relacionamentos')->onDelete('cascade');
            $table->foreignId('tipo_relacionamento_id')->constrained('tipos_relacionamento')->onDelete('cascade');
            $table->primary(['relacionamento_id', 'tipo_relacionamento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relacionamento_tipo');
    }
};
