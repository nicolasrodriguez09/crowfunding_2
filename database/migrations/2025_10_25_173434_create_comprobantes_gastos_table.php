<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comprobantes_gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->string('url_documento', 2048);
            $table->dateTime('fecha_subida')->useCurrent();
            $table->string('estado_verificacion', 20)->default('pendiente')->index(); // pendiente/verificado/rechazado
            $table->text('notas_auditoria')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('comprobantes_gastos');
    }
};
