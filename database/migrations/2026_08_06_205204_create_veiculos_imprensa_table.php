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
        Schema::create('veiculos_imprensa', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('tipo', 50); // radio, tv, jornal, portal, blog, podcast, outro
            $table->string('cidade', 100)->nullable();
            $table->string('site', 255)->nullable();
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
        Schema::dropIfExists('veiculos_imprensa');
    }
};
