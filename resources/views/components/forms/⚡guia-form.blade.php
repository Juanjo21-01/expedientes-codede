<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Guia;

new class extends Component {
    use WithFileUploads;

    public ?int $guiaId = null;
    public bool $modoEdicion = false;

    // Campos del formulario
    public string $titulo = '';
    public string $categoriaSeleccionada = '';
    public string $nuevaCategoria = '';
    public bool $esNuevaCategoria = false;
    public string $fecha_publicacion = '';
    public $archivo_pdf = null;

    // Para confirmación (Director / Jefe Financiero)
    public bool $mostrarConfirmacion = false;

    // Versión calculada (solo lectura)
    public int $versionCalculada = 1;

    // Categoría actual (solo en edición, readonly)
    public string $categoriaActual = '';

    public function mount(?int $guiaId = null)
    {
        if ($guiaId) {
            $this->guiaId = $guiaId;
            $this->modoEdicion = true;

            $guia = Guia::findOrFail($guiaId);
            $this->titulo = $guia->titulo;
            $this->categoriaActual = $guia->categoria;
            $this->categoriaSeleccionada = $guia->categoria;
            $this->fecha_publicacion = $guia->fecha_publicacion->format('Y-m-d');
            $this->versionCalculada = $guia->version;
        } else {
            $this->fecha_publicacion = now()->format('Y-m-d');
        }
    }

    #[Computed]
    public function categorias()
    {
        return Guia::categoriasDisponibles();
    }

    // Al cambiar la categoría seleccionada
    public function updatedCategoriaSeleccionada($value)
    {
        if ($value === '__nueva__') {
            $this->esNuevaCategoria = true;
            $this->nuevaCategoria = '';
            $this->versionCalculada = 1;
        } else {
            $this->esNuevaCategoria = false;
            $this->nuevaCategoria = '';
            if ($value) {
                $this->versionCalculada = Guia::siguienteVersion($value);
            } else {
                $this->versionCalculada = 1;
            }
        }
    }

    // Al escribir nueva categoría
    public function updatedNuevaCategoria()
    {
        $this->versionCalculada = 1;
        $normalizada = Guia::normalizarCategoria($this->nuevaCategoria);
        // Si ya existe, calcular versión
        if ($normalizada && in_array($normalizada, $this->categorias)) {
            $this->versionCalculada = Guia::siguienteVersion($normalizada);
        }
    }

    // Determinar categoría final
    private function getCategoriaFinal(): string
    {
        if ($this->modoEdicion) {
            return $this->categoriaActual;
        }
        if ($this->esNuevaCategoria) {
            return Guia::normalizarCategoria($this->nuevaCategoria);
        }
        return $this->categoriaSeleccionada;
    }

    // Si es Director o Jefe, mostrar confirmación antes de guardar
    public function intentarGuardar()
    {
        $user = auth()->user();
        if (!$user->isAdmin() && ($user->isDirector() || $user->isJefeFinanciero())) {
            // Validar primero
            $this->validarCampos();
            $this->mostrarConfirmacion = true;
            return;
        }

        $this->guardar();
    }

    public function cancelarConfirmacion()
    {
        $this->mostrarConfirmacion = false;
    }

    public function confirmarYGuardar()
    {
        $this->mostrarConfirmacion = false;
        $this->guardar();
    }

    private function validarCampos()
    {
        $rules = [
            'titulo' => 'required|string|max:100',
            'fecha_publicacion' => 'required|date',
        ];

        if (!$this->modoEdicion) {
            $rules['archivo_pdf'] = 'required|file|mimes:pdf|max:10240';

            if ($this->esNuevaCategoria) {
                $rules['nuevaCategoria'] = 'required|string|max:100';
            } else {
                $rules['categoriaSeleccionada'] = 'required|string';
            }
        } else {
            // En edición, PDF es opcional (reemplazar)
            if ($this->archivo_pdf) {
                $rules['archivo_pdf'] = 'file|mimes:pdf|max:10240';
            }
        }

        return $this->validate($rules, [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede exceder 100 caracteres.',
            'archivo_pdf.required' => 'Debe seleccionar un archivo PDF.',
            'archivo_pdf.mimes' => 'Solo se permiten archivos PDF.',
            'archivo_pdf.max' => 'El archivo no puede exceder 10 MB.',
            'fecha_publicacion.required' => 'La fecha de publicación es obligatoria.',
            'categoriaSeleccionada.required' => 'Debe seleccionar una categoría.',
            'nuevaCategoria.required' => 'Debe escribir el nombre de la nueva categoría.',
        ]);
    }

    public function guardar()
    {
        $this->validarCampos();

        $categoria = $this->getCategoriaFinal();

        if ($this->modoEdicion) {
            // --- EDITAR ---
            $guia = Guia::findOrFail($this->guiaId);

            $guia->titulo = $this->titulo;
            $guia->fecha_publicacion = $this->fecha_publicacion;

            // Si se sube un nuevo PDF, reemplazar
            if ($this->archivo_pdf) {
                $guia->eliminarArchivo();
                $nombreArchivo = Guia::generarNombreArchivo($guia->categoria);
                $this->archivo_pdf->storeAs('guia', $nombreArchivo, 'public');
                $guia->archivo_pdf = $nombreArchivo;
            }

            $guia->save();

            $this->redirectRoute('admin.guias.index', navigate: true);
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Guía '{$guia->titulo}' actualizada correctamente.");
        } else {
            // --- CREAR ---
            // Validar límite de versiones
            if (!Guia::puedeAgregarVersion($categoria) && Guia::contarVersiones($categoria) > 0) {
                $this->addError('categoriaSeleccionada', "La categoría '{$categoria}' ya tiene el máximo de " . Guia::MAX_VERSIONES_POR_CATEGORIA . ' versiones.');
                return;
            }

            $version = Guia::siguienteVersion($categoria);
            $nombreArchivo = Guia::generarNombreArchivo($categoria);

            // Guardar archivo en storage/app/public/guia/
            $this->archivo_pdf->storeAs('guia', $nombreArchivo, 'public');

            // Desactivar versiones anteriores de esta categoría
            Guia::desactivarCategoria($categoria);

            // Crear registro
            Guia::create([
                'titulo' => $this->titulo,
                'archivo_pdf' => $nombreArchivo,
                'version' => $version,
                'categoria' => $categoria,
                'estado' => true,
                'fecha_publicacion' => $this->fecha_publicacion,
            ]);

            $this->redirectRoute('admin.guias.index', navigate: true);
            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Guía subida exitosamente. Versión ' . $version . " de \"" . $categoria . "\".");
        }
    }

    public function cancelar()
    {
        $this->redirectRoute('admin.guias.index', navigate: true);
    }
};
?>

<div>
    <form wire:submit="intentarGuardar" class="space-y-6">
        {{-- Sección: Categoría y Versión --}}
        <div class="card bg-base-200 shadow-sm border border-base-300 rounded-lg">
            <div class="card-body">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-tag class="w-5 h-5 text-primary" />
                    Categoría y Versión
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Categoría (solo en modo crear) --}}
                    @if (!$modoEdicion)
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend">Categoría <span class="text-error">*</span></legend>
                            <select wire:model.live="categoriaSeleccionada" id="categoriaSeleccionada"
                                class="select w-full @error('categoriaSeleccionada') select-error @enderror">
                                <option value="" selected disabled>Seleccionar categoría...</option>
                                @foreach ($this->categorias as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                                <option value="__nueva__">+ Nueva categoría</option>
                            </select>
                            @error('categoriaSeleccionada')
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                            <p class="label text-base-content/50">
                                Agrupar guías del mismo tipo bajo una categoría
                            </p>
                        </fieldset>
                    @else
                        {{-- En edición, categoría es de solo lectura --}}
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend">Categoría</legend>
                            <input type="text" value="{{ $categoriaActual }}" class="input w-full" disabled />
                            <p class="label text-base-content/50">La categoría no se puede cambiar</p>
                        </fieldset>
                    @endif

                    {{-- Versión auto-calculada --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Versión</legend>
                        <input type="text" value="v{{ $versionCalculada }}" class="input w-full font-mono"
                            disabled />
                        <p class="label text-base-content/50">
                            @if ($modoEdicion)
                                Versión actual
                            @else
                                Se calcula automáticamente según la categoría
                            @endif
                        </p>
                    </fieldset>

                    {{-- Input nueva categoría (ocupa toda la fila) --}}
                    @if (!$modoEdicion && $esNuevaCategoria)
                        <fieldset class="fieldset w-full md:col-span-2">
                            <legend class="fieldset-legend">Nombre de la nueva categoría <span
                                    class="text-error">*</span></legend>
                            <input type="text" wire:model.live.debounce.300ms="nuevaCategoria" id="nuevaCategoria"
                                class="input w-full @error('nuevaCategoria') input-error @enderror"
                                placeholder="Ej: GUÍA DE LLENADO DE EXPEDIENTES" maxlength="100" />
                            @error('nuevaCategoria')
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                            <p class="label text-base-content/50">
                                Se guardará en mayúsculas automáticamente
                            </p>
                        </fieldset>
                    @endif
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Sección: Información de la Guía --}}
        <div class="card bg-base-200 shadow-sm border border-base-300 rounded-lg">
            <div class="card-body">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                    Información de la Guía
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Título <span class="text-error">*</span></legend>
                        <input type="text" wire:model="titulo" id="titulo"
                            class="input w-full @error('titulo') input-error @enderror"
                            placeholder="Ej: Guía de Llenado de Expedientes" maxlength="100" />
                        @error('titulo')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Fecha de Publicación <span class="text-error">*</span></legend>
                        <input type="date" wire:model="fecha_publicacion" id="fecha_publicacion"
                            class="input w-full @error('fecha_publicacion') input-error @enderror" />
                        @error('fecha_publicacion')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Sección: Archivo PDF --}}
        <div class="card bg-base-200 shadow-sm border border-base-300 rounded-lg">
            <div class="card-body">
                <h3 class="font-semibold text-lg flex items-center gap-2 mb-4">
                    <x-heroicon-o-document-arrow-up class="w-5 h-5 text-primary" />
                    Archivo PDF
                    @if ($modoEdicion)
                        <span class="badge badge-sm badge-ghost">Opcional</span>
                    @endif
                </h3>

                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend">
                        Documento PDF
                        @if (!$modoEdicion)
                            <span class="text-error">*</span>
                        @endif
                    </legend>

                    <div
                        class="border-2 border-dashed border-base-content/10 rounded-lg p-6 text-center hover:border-primary/50 transition-colors @error('archivo_pdf') border-error @enderror">
                        <x-heroicon-o-cloud-arrow-up class="w-10 h-10 mx-auto text-base-content/30 mb-3" />

                        <input type="file" wire:model="archivo_pdf" accept=".pdf"
                            class="file-input file-input-sm w-full max-w-sm" />

                        <div class="mt-3 text-xs text-base-content/50 space-y-1">
                            <p>Solo archivos PDF, máximo 10 MB</p>
                            @if ($modoEdicion)
                                <p class="text-warning">Si sube un nuevo archivo, se reemplazará el actual</p>
                            @endif
                        </div>

                        {{-- Preview del archivo seleccionado --}}
                        @if ($archivo_pdf)
                            <div class="mt-3 flex items-center justify-center gap-2 text-sm text-success">
                                <x-heroicon-o-check-circle class="w-5 h-5" />
                                <span>{{ $archivo_pdf->getClientOriginalName() }}
                                    ({{ number_format($archivo_pdf->getSize() / 1024 / 1024, 2) }} MB)</span>
                            </div>
                        @endif

                        {{-- Spinner de carga --}}
                        <div wire:loading wire:target="archivo_pdf" class="mt-3">
                            <span class="loading loading-spinner loading-sm text-primary"></span>
                            <span class="text-sm text-primary ml-2">Cargando archivo...</span>
                        </div>
                    </div>

                    @error('archivo_pdf')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </div>

        {{-- Botones --}}
        <div class="divider"></div>
        <div class="flex justify-end gap-3">
            <button type="button" wire:click="cancelar" class="btn btn-ghost">Cancelar</button>
            <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled"
                wire:target="archivo_pdf,intentarGuardar">
                <span wire:loading wire:target="intentarGuardar" class="loading loading-spinner loading-sm"></span>
                <x-heroicon-o-arrow-up-tray class="w-5 h-5" wire:loading.remove wire:target="intentarGuardar" />
                {{ $modoEdicion ? 'Guardar Cambios' : 'Subir Guía' }}
            </button>
        </div>
    </form>

    {{-- Modal de confirmación (Director / Jefe Financiero) --}}
    @if ($mostrarConfirmacion)
        <div class="modal modal-open">
            <div class="modal-box max-w-md">
                <div class="flex items-center gap-3 mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-warning/10 text-warning rounded-lg w-10 h-10">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Confirmar Publicación</h3>
                        <p class="text-sm text-base-content/60">Revise la información antes de publicar</p>
                    </div>
                </div>

                <div class="bg-base-200 rounded-lg p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Título:</span>
                        <span class="font-medium">{{ $titulo }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Categoría:</span>
                        <span class="font-medium">{{ $this->getCategoriaFinal() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Versión:</span>
                        <span class="font-mono">v{{ $versionCalculada }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Fecha:</span>
                        <span>{{ $fecha_publicacion }}</span>
                    </div>
                    @if ($archivo_pdf)
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Archivo:</span>
                            <span>{{ $archivo_pdf->getClientOriginalName() }}</span>
                        </div>
                    @endif
                </div>

                <div role="alert" class="alert alert-info mt-4">
                    <x-heroicon-o-information-circle class="stroke-current shrink-0 w-5 h-5" />
                    <span class="text-xs">La guía anterior de esta categoría será desactivada automáticamente.</span>
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="cancelarConfirmacion" class="btn btn-ghost">
                        Revisar
                    </button>
                    <button type="button" wire:click="confirmarYGuardar" class="btn btn-primary gap-2"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmarYGuardar"
                            class="loading loading-spinner loading-sm"></span>
                        Confirmar y Publicar
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="cancelarConfirmacion">close</button>
            </form>
        </div>
    @endif
</div>
