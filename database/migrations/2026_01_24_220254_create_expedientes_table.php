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
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_snip')->unique();
            $table->string('nombre_proyecto');
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->foreignId('responsable_id')->constrained('users');
            $table->foreignId('tipo_solicitud_id')->constrained('tipo_solicitudes');
            $table->enum('ordinario_extraordinario', ['ORDINARIO', 'EXTRAORDINARIO', 'ASIGNACION EXTRAORDINARIA']);
            $table->date('fecha_recibido');
            $table->enum('estado', ['Borrador', 'En revisión', 'Aprobado', 'Rechazado', 'Archivado'])->default('Borrador');
            $table->date('fecha_aprobacion')->nullable();
            $table->decimal('monto_contrato', 15, 2)->nullable();
            $table->string('adjudicatario', 100)->nullable();
            $table->string('observaciones')->nullable();
            $table->json('etiquetas')->nullable();
            $table->timestamp('fecha_ultima_actualizacion')->useCurrent();

            // Campos financieros actuales (híbrido)
            $table->enum('financiero_estado', ['Pendiente', 'Completo', 'Incompleto'])->default('Pendiente');
            $table->foreignId('financiero_revisor_id')->nullable()->constrained('users');
            $table->dateTime('financiero_fecha_recepcion')->nullable();
            $table->dateTime('financiero_fecha_revision')->nullable();
            $table->dateTime('financiero_fecha_complemento')->nullable();
            $table->decimal('financiero_monto_aprobado', 15, 2)->nullable();
            $table->string('financiero_comentarios')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
