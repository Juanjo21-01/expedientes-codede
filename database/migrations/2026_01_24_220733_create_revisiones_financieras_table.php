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
        Schema::create('revisiones_financieras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->onDelete('cascade');
            $table->foreignId('revisor_id')->constrained('users');
            $table->enum('estado', ['Completo', 'Incompleto']);
            $table->enum('accion', ['Aprobar', 'Rechazar', 'SolicitarCorrecciones'])->nullable();
            $table->decimal('monto_aprobado', 15, 2)->nullable();
            $table->string('observaciones')->nullable();
            $table->dateTime('fecha_revision');
            $table->dateTime('fecha_complemento')->nullable();
            $table->integer('dias_transcurridos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisiones_financieras');
    }
};
