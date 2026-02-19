<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salas', function (Blueprint $table) {
        
            if (!Schema::hasColumn('salas', 'sala_id')) {
                $table->foreignId('sala_id')->nullable()->constrained();
            }
        });
    }

    
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            if (Schema::hasColumn('salas', 'sala_id')) {
                $table->dropForeign(['sala_id']);
                $table->dropColumn('sala_id');
            }
        });
    }
};
