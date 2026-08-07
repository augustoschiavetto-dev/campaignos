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
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('favoritavel_type', 100);
            $table->unsignedBigInteger('favoritavel_id');
            $table->timestamps();
            
            $table->unique(['user_id', 'favoritavel_type', 'favoritavel_id']);
        });

        Schema::create('historico_recente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('acessavel_type', 100);
            $table->unsignedBigInteger('acessavel_id');
            $table->string('titulo', 150);
            $table->string('url', 255);
            $table->timestamp('visited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'acessavel_type', 'acessavel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_recente');
        Schema::dropIfExists('favoritos');
    }
};
