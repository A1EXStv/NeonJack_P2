<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
        
            if (!Schema::hasColumn('users', 'skin_activa')) {
                $table->foreignId('skin_activa')->nullable()->constrained('skins')->default(1);
            }
        });
    }

    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'skin_activa')) {
                $table->dropForeign(['skin_activa']);
                $table->dropColumn('skin_activa');
            }
        });
    }
};
