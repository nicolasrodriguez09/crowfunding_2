<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 150);
            $table->string('email')->unique();
            $table->string('password'); // por "Contraseña"
            $table->boolean('estado_verificacion')->default(false)->index();
            $table->text('info_personal')->nullable();
            $table->json('redes_sociales')->nullable(); // por "RedSociales"
            $table->unsignedTinyInteger('indice_confianza')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuarios');
    }
};
