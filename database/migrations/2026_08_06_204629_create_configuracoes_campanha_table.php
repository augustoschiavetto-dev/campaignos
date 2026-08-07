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
        Schema::create('configuracoes_campanha', function (Blueprint $table) {
            $table->id();
            $table->string('nome_campanha', 150);
            $table->string('candidato_nome', 150);
            $table->string('candidato_nome_politico', 100);
            $table->string('candidato_cargo', 100)->default('Deputado Federal');
            $table->string('candidato_numero', 20);
            $table->string('partido_sigla', 20);
            $table->string('partido_coligacao', 255)->nullable();
            $table->string('campanha_cnpj', 20)->nullable();
            $table->string('campanha_cidade', 100)->default('Limeira');
            $table->string('campanha_uf', 2)->default('SP');
            $table->date('data_primeiro_turno');
            $table->string('campanha_timezone', 50)->default('America/Sao_Paulo');
            $table->string('campanha_telefone', 20)->nullable();
            $table->string('campanha_email', 100)->nullable();
            $table->string('campanha_site', 255)->nullable();
            $table->string('campanha_instagram', 255)->nullable();
            $table->string('campanha_facebook', 255)->nullable();
            $table->string('campanha_youtube', 255)->nullable();
            $table->string('identidade_cor_primaria', 20)->default('#1e3a8a');
            $table->string('identidade_cor_secundaria', 20)->default('#10b981');
            $table->string('identidade_logo_path', 255)->nullable();
            $table->text('texto_institucional_curto')->nullable();
            $table->string('preferencia_tema', 10)->default('escuro'); // claro, escuro
            $table->json('prioridades_dia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes_campanha');
    }
};
