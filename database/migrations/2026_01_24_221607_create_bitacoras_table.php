<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->enum('entidad', ['Guía', 'Expediente', 'Auditoría', 'Usuario', 'Notificación']);
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->enum('tipo', ['Creación', 'Eliminación', 'Edición', 'Reporte', 'Cambio de Estado', 'Revisión', 'Notificación']);
            $table->text('detalle');
            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index(['entidad', 'entidad_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
