<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aportaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->nullable()
                  ->constrained('usuarios')->nullOnDelete(); // FK ID_Colaborador
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->decimal('monto', 15, 2);
            $table->dateTime('fecha_aportacion')->useCurrent();
            $table->string('estado_pago', 20)->default('pendiente')->index(); // pendiente/pagado/fallido
            $table->string('id_transaccion_pago', 100)->nullable()->unique();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('aportaciones');
    }
};
