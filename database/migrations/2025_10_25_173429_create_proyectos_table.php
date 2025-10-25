<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creador_id')->constrained('usuarios')->cascadeOnDelete(); // FK ID_Creador
            $table->string('titulo');
            $table->longText('descripcion_proyecto')->nullable();
            $table->decimal('meta_financiacion', 15, 2)->default(0);
            $table->decimal('monto_recaudado', 15, 2)->default(0);
            $table->dateTime('fecha_limite')->nullable();
            $table->json('cronograma')->nullable();
            $table->json('presupuesto')->nullable();
            $table->string('estado', 32)->default('borrador')->index();
            $table->string('modelo_financiamiento', 32)->nullable()->index();
            $table->string('categoria', 64)->nullable()->index();
            $table->string('ubicacion_geografica', 120)->nullable();
            $table->timestamps(); // reemplaza "FechaCreacion"
        });
    }
    public function down(): void {
        Schema::dropIfExists('proyectos');
    }
};
