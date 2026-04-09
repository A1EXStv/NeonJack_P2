<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->foreignId('sala_id')->nullable()->constrained('salas')->nullOnDelete()->after('id');
            $table->foreignId('user_id')->nullable()->change();
            $table->json('mano_dealer')->default('[]')->change();
        });
    }

    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->dropForeign(['sala_id']);
            $table->dropColumn('sala_id');
        });
    }
};
