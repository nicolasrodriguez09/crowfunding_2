<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('solicitudes_desembolso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->decimal('monto_solicitado', 15, 2);
            $table->longText('justificacion')->nullable();
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->string('estado', 20)->default('pendiente')->index(); // pendiente/aprobado/rechazado
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('solicitudes_desembolso');
    }
};
