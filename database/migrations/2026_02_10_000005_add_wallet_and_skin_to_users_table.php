<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wallet')) {
                $table->decimal('wallet', 10, 2)->default(0)->after('email');
            }
            if (!Schema::hasColumn('users', 'active_skin_id')) {
                $table->foreignId('active_skin_id')->nullable()->after('wallet')->constrained('skins')->onDelete('set null');
            }
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'active_skin_id')) {
                $table->dropForeign(['active_skin_id']);
                $table->dropColumn('active_skin_id');
            }
            if (Schema::hasColumn('users', 'wallet')) {
                $table->dropColumn('wallet');
            }
        });
    }
};
