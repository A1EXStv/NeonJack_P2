<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->string('code', 8)->unique()->after('nombre_sala');
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting')->after('code');
            $table->unsignedTinyInteger('max_players')->default(3)->after('status');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete()->after('max_players');
        });
 
        //Rellenar code en filas existentes (no pueden ser null ni duplicadas)
        \DB::table('salas')->orderBy('id')->each(function ($sala) {
            \DB::table('salas')->where('id', $sala->id)->update(['code' => strtoupper('BJ-' . \Illuminate\Support\Str::random(4))]);
        });
 
        // Todas las filas tienen code, hacer la columna NOT NULL
        Schema::table('salas', function (Blueprint $table) {
            $table->string('code', 8)->nullable(false)->change();
        });
 
        // Crear tabla pivote sala_usuario
        Schema::create('sala_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['sitting', 'ready', 'playing', 'spectating'])->default('sitting');
            $table->unsignedInteger('chips')->default(1000);
            $table->unsignedTinyInteger('seat')->nullable();
            $table->timestamps();
 
            $table->unique(['sala_id', 'user_id']);
            $table->unique(['sala_id', 'seat']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('sala_usuario');
 
        Schema::table('salas', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'status', 'max_players', 'owner_id']);
        });
    }
};
