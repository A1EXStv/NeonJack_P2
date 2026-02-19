<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('apellido1');
            $table->string('apellido2')->nullable();
            $table->string('alias')->unique()->nullable();
            $table->string('correo')->unique();
            $table->timestamp('verificar_correo')->nullable();
            $table->string('contrasena');
            $table->decimal('cartera', 10, 2);
            $table->foreignId('skin_id')->nullable()->constrained('skins')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
