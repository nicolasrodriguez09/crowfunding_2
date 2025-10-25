<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('colaborador_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->unsignedTinyInteger('puntaje'); // 1..5 típico
            $table->text('comentarios')->nullable();
            $table->dateTime('fecha_calificacion')->useCurrent();
            $table->timestamps();

            // Evita múltiples calificaciones del mismo colaborador al mismo proyecto
            $table->unique(['proyecto_id', 'colaborador_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('calificaciones');
    }
};
