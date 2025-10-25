<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes_gastos')->cascadeOnDelete(); // FK ID_Comprobante
            $table->foreignId('auditor_id')->nullable()->constrained('usuarios')->nullOnDelete(); // FK ID_Auditor
            $table->dateTime('fecha_revision')->useCurrent();
            $table->string('resultado', 20)->default('pendiente')->index(); // aprobado/rechazado/pendiente
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('auditorias');
    }
};
