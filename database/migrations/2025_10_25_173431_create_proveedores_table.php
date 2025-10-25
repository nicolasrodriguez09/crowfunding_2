<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creador_id')->constrained('usuarios')->cascadeOnDelete(); // FK ID_Creador
            $table->string('nombre_proveedor');
            $table->text('info_contacto')->nullable();
            $table->string('especialidad', 120)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('proveedores');
    }
};
