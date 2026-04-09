<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manos', function (Blueprint $table) {
            $table->foreignId('partida_id')->nullable()->constrained('partidas')->nullOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('manos', function (Blueprint $table) {
            $table->dropForeign(['partida_id']);
            $table->dropColumn('partida_id');
        });
    }
};
