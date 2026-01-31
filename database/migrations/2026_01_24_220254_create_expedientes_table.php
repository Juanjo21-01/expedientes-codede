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
            $table->string('codigo_snip')->unique(); // Ya tiene índice por ser unique
            $table->string('nombre_proyecto');
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->foreignId('responsable_id')->constrained('users');
            $table->foreignId('tipo_solicitud_id')->constrained('tipo_solicitudes');
            $table->enum('ordinario_extraordinario', ['ORDINARIO', 'EXTRAORDINARIO', 'ASIGNACION EXTRAORDINARIA']);
            $table->date('fecha_recibido');
            
            // Estados del expediente:
            // - Recibido: Expediente ingresado al sistema
            // - En Revisión: Siendo revisado por el área técnica o financiera
            // - Completo: Revisión financiera completada, documentación correcta
            // - Incompleto: Falta documentación o hay observaciones pendientes
            // - Aprobado: Expediente aprobado para pago/desembolso
            // - Rechazado: Expediente rechazado definitivamente
            // - Archivado: Expediente cerrado y archivado
            $table->enum('estado', [
                'Recibido',
                'En Revisión',
                'Completo',
                'Incompleto', 
                'Aprobado',
                'Rechazado',
                'Archivado'
            ])->default('Recibido');
            
            $table->date('fecha_aprobacion')->nullable();
            $table->decimal('monto_contrato', 15, 2)->nullable();
            $table->string('adjudicatario', 100)->nullable();
            $table->string('observaciones')->nullable();
            $table->json('etiquetas')->nullable();
            $table->timestamp('fecha_ultima_actualizacion')->useCurrent();
            
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('estado');
            $table->index('fecha_recibido');
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
