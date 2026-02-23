<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
        
            if (!Schema::hasColumn('users', 'active_skin')) {
                $table->foreignId('active_skin')->nullable()->constrained('skins')->default(1);
            }
        });
    }

    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sactive_skina')) {
                $table->dropForeign(['active_skin']);
                $table->dropColumn('active_skin');
            }
        });
    }
};
