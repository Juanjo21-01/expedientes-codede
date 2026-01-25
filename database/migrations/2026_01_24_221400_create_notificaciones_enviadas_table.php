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
        Schema::create('notificaciones_enviadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_notificacion_id')->nullable()->constrained('tipo_notificaciones');
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes');
            $table->string('destinatario_email');
            $table->text('mensaje');
            $table->timestamp('enviado_at')->nullable();
            $table->enum('estado', ['Pendiente', 'Enviado', 'Fallido'])->default('Pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_enviadas');
    }
};
