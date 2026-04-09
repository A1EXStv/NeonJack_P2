<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partida_usuario', function (Blueprint $table) {
            $table->string('estado')->default('apostando')->after('user_id');
            $table->decimal('apuesta_total', 10, 2)->default(0)->change();
            $table->string('resultado')->nullable()->change();
            $table->decimal('balance_resultado', 10, 2)->nullable()->change();
            $table->json('mano_usuario')->default('[]')->change();
        });
    }

    public function down(): void
    {
        Schema::table('partida_usuario', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
