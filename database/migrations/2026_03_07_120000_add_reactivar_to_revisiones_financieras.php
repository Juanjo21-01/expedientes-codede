<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE revisiones_financieras MODIFY COLUMN accion ENUM('Aprobar','Rechazar','SolicitarCorrecciones','Reactivar') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE revisiones_financieras MODIFY COLUMN accion ENUM('Aprobar','Rechazar','SolicitarCorrecciones') NULL");
    }
};
