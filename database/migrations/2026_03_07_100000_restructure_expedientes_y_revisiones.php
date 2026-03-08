<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reestructuración del módulo de expedientes:
     * 
     * 1. Eliminar campo `adjudicatario` de expedientes (redundante con responsable)
     * 2. Eliminar FK `tipo_solicitud_id` de expedientes (se mueve a revisiones_financieras)
     * 3. Agregar `aporte_municipalidad` a expedientes
     * 4. Actualizar estados del expediente (quitar Completo/Incompleto)
     * 5. Agregar `tipo_solicitud_id` a revisiones_financieras
     * 6. Agregar `numero_revision` a revisiones_financieras (para tracking secuencial)
     */
    public function up(): void
    {
        // --- Expedientes ---

        // Actualizar estados: quitar Completo e Incompleto del expediente
        // Los estados del expediente ahora son: Recibido, En Revisión, Aprobado, Rechazado, Archivado
        // Primero migrar datos existentes con estados obsoletos
        DB::table('expedientes')->where('estado', 'Completo')->update(['estado' => 'En Revisión']);
        DB::table('expedientes')->where('estado', 'Incompleto')->update(['estado' => 'En Revisión']);

        Schema::table('expedientes', function (Blueprint $table) {
            // Eliminar adjudicatario
            $table->dropColumn('adjudicatario');

            // Eliminar relación con tipo_solicitud
            $table->dropForeign(['tipo_solicitud_id']);
            $table->dropColumn('tipo_solicitud_id');

            // Agregar aporte de la municipalidad
            $table->decimal('aporte_municipalidad', 15, 2)->nullable()->after('monto_contrato');
        });

        // Cambiar el enum de estado (MySQL requiere ALTER COLUMN)
        DB::statement("ALTER TABLE expedientes MODIFY COLUMN estado ENUM('Recibido', 'En Revisión', 'Aprobado', 'Rechazado', 'Archivado') DEFAULT 'Recibido'");

        // --- Revisiones Financieras ---

        Schema::table('revisiones_financieras', function (Blueprint $table) {
            // Agregar tipo de solicitud (desembolso) a cada revisión
            $table->foreignId('tipo_solicitud_id')
                ->nullable()
                ->after('expediente_id')
                ->constrained('tipo_solicitudes');

            // Número de revisión dentro del expediente (1, 2, 3, 4...)
            $table->unsignedTinyInteger('numero_revision')
                ->default(1)
                ->after('tipo_solicitud_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // --- Revisiones Financieras: revertir ---
        Schema::table('revisiones_financieras', function (Blueprint $table) {
            $table->dropForeign(['tipo_solicitud_id']);
            $table->dropColumn(['tipo_solicitud_id', 'numero_revision']);
        });

        // --- Expedientes: revertir ---
        DB::statement("ALTER TABLE expedientes MODIFY COLUMN estado ENUM('Recibido', 'En Revisión', 'Completo', 'Incompleto', 'Aprobado', 'Rechazado', 'Archivado') DEFAULT 'Recibido'");

        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropColumn('aporte_municipalidad');

            $table->foreignId('tipo_solicitud_id')
                ->nullable()
                ->after('responsable_id')
                ->constrained('tipo_solicitudes');

            $table->string('adjudicatario', 100)->nullable()->after('monto_contrato');
        });
    }
};
