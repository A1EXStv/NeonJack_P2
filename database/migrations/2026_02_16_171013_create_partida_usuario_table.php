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
        Schema::create('partida_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas', 'id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users', 'id') ->onDelete('cascade');
            $table->decimal('apuesta_total', 10, 2);
            $table->string('mano_usuario');
            $table->string('resultado');
            $table->decimal('balance_resultado', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partida_usuario');
    }
};
