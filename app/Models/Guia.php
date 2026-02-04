<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Guia extends Model
{
    // Nombre de la tabla
    protected $table = 'guias';

    // Atributos asignables
    protected $fillable = [
        'titulo',
        'archivo_pdf',
        'version',
        'fecha_publicacion',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'date',
        ];
    }

    // ---- Scopes ----

    /**
     * Ordenar por fecha de publicación (más recientes primero)
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderBy('fecha_publicacion', 'desc');
    }

    /**
     * Ordenar por versión
     */
    public function scopePorVersion(Builder $query): Builder
    {
        return $query->orderBy('version', 'desc');
    }

    /**
     * Buscar por título
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where('titulo', 'like', "%{$termino}%");
    }

    /**
     * Publicadas en un año específico
     */
    public function scopeDeAnio(Builder $query, int $anio): Builder
    {
        return $query->whereYear('fecha_publicacion', $anio);
    }

    // ---- Accesores ----

    /**
     * URL pública del PDF
     */
    public function getUrlPdfAttribute(): string
    {
        return asset("guia/{$this->archivo_pdf}");
    }

    /**
     * Ruta completa del archivo
     */
    public function getRutaArchivoAttribute(): string
    {
        return public_path("guia/{$this->archivo_pdf}");
    }

    /**
     * Título con versión
     */
    public function getTituloCompletoAttribute(): string
    {
        return "{$this->titulo} (v{$this->version})";
    }

    /**
     * Verifica si el archivo existe
     */
    public function archivoExiste(): bool
    {
        return file_exists($this->ruta_archivo);
    }

    /**
     * Tamaño del archivo en formato legible
     */
    public function getTamanioArchivoAttribute(): string
    {
        if (!$this->archivoExiste()) {
            return 'N/A';
        }

        $bytes = filesize($this->ruta_archivo);
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // ---- Métodos estáticos ----

    /**
     * Obtener la guía más reciente
     */
    public static function masReciente(): ?self
    {
        return self::recientes()->first();
    }

    /**
     * Obtener la última versión
     */
    public static function ultimaVersion(): ?self
    {
        return self::porVersion()->first();
    }
}
