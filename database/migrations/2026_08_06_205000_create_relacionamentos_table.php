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
        Schema::create('relacionamentos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_pessoa', 2)->default('PF'); // PF ou PJ
            $table->string('nome', 150);
            $table->string('apelido', 100)->nullable();
            $table->string('cpf_cnpj', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('telefone_normalizado', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('profissao', 100)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->foreignId('bairro_id')->nullable()->constrained('bairros')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('ativo'); // ativo, inativo
            $table->date('data_proxima_acao')->nullable();
            $table->text('descricao_proxima_acao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relacionamentos');
    }
};
