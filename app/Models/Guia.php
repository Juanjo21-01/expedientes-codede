<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Guia extends Model
{
    // Nombre de la tabla
    protected $table = 'guias';

    // ---- Constantes de Estado ----
    public const ESTADO_ACTIVO = true;
    public const ESTADO_INACTIVO = false;

    // Máximo de versiones por categoría
    public const MAX_VERSIONES_POR_CATEGORIA = 10;

    // Atributos asignables
    protected $fillable = [
        'titulo',
        'archivo_pdf',
        'version',
        'categoria',
        'estado',
        'fecha_publicacion',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'date',
            'estado' => 'boolean',
        ];
    }

    // ---- Scopes ----

    /**
     * Solo guías activas
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', true);
    }

    /**
     * Solo guías inactivas
     */
    public function scopeInactivas(Builder $query): Builder
    {
        return $query->where('estado', false);
    }

    /**
     * Filtrar por categoría
     */
    public function scopeDeCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Ordenar por fecha de publicación (más recientes primero)
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderBy('fecha_publicacion', 'desc');
    }

    /**
     * Ordenar por versión descendente
     */
    public function scopePorVersion(Builder $query): Builder
    {
        return $query->orderBy('version', 'desc');
    }

    /**
     * Buscar por título o categoría
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('titulo', 'like', "%{$termino}%")
              ->orWhere('categoria', 'like', "%{$termino}%");
        });
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

    // ---- Métodos de Categoría ----

    /**
     * Normalizar categoría: mayúsculas + trim
     */
    public static function normalizarCategoria(string $categoria): string
    {
        return mb_strtoupper(trim($categoria));
    }

    /**
     * Obtener categorías únicas existentes
     */
    public static function categoriasDisponibles(): array
    {
        return self::distinct()->pluck('categoria')->sort()->values()->toArray();
    }

    /**
     * Obtener la siguiente versión para una categoría
     */
    public static function siguienteVersion(string $categoria): int
    {
        return self::deCategoria($categoria)->count() + 1;
    }

    /**
     * Contar versiones de una categoría
     */
    public static function contarVersiones(string $categoria): int
    {
        return self::deCategoria($categoria)->count();
    }

    /**
     * Verificar si una categoría puede aceptar más versiones
     */
    public static function puedeAgregarVersion(string $categoria): bool
    {
        return self::contarVersiones($categoria) < self::MAX_VERSIONES_POR_CATEGORIA;
    }

    /**
     * Desactivar todas las versiones activas de una categoría
     */
    public static function desactivarCategoria(string $categoria): void
    {
        self::deCategoria($categoria)->activas()->update(['estado' => false]);
    }

    /**
     * Generar nombre de archivo único para el PDF
     */
    public static function generarNombreArchivo(string $categoria, int $version): string
    {
        $slug = Str::slug($categoria);
        $timestamp = now()->timestamp;
        return "{$slug}_v{$version}_{$timestamp}.pdf";
    }

    /**
     * Eliminar el archivo PDF del disco
     */
    public function eliminarArchivo(): bool
    {
        if ($this->archivoExiste()) {
            return unlink($this->ruta_archivo);
        }
        return false;
    }

    // ---- Métodos estáticos ----

    /**
     * Obtener la guía activa de cada categoría (para vista pública)
     */
    public static function activasPorCategoria()
    {
        return self::activas()->recientes()->get()->unique('categoria');
    }

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
