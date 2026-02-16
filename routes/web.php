<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Expediente;
use App\Models\Municipio;
use App\Models\Guia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Redirigir a login
Route::get('/', fn() => redirect()->route('login'))->name('inicio');

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'usuario_activo'])->group(function () {

    // Redireccionar al dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));
    /*
    |----------------------------------------------------------------------
    | Dashboard - Todos los usuarios autenticados
    |----------------------------------------------------------------------
    */
    Route::view('dashboard', 'pages.dashboard')->name('dashboard');


    /*
    |----------------------------------------------------------------------
    | Guías - Todos pueden ver las guías activas
    |----------------------------------------------------------------------
    */
    Route::livewire('/guias', 'pages::guia.index')->name('guias');

    /*
    |----------------------------------------------------------------------
    | Perfil - Todos pueden ver/editar su perfil
    |----------------------------------------------------------------------
    */
    Route::livewire('/perfil', 'pages::perfil.show')->name('perfil');

    /*
    |----------------------------------------------------------------------
    | Expedientes - Acceso según rol y municipio asignado
    |----------------------------------------------------------------------
    */
    Route::prefix('expedientes')->name('expedientes.')->group(function () {
        
        // Listado de expedientes
        Route::livewire('/', 'pages::expedientes.index')->name('index');

        // Crear expediente (Técnico / Admin)
        Route::livewire('/crear', 'pages::expedientes.create')
            ->can('create', Expediente::class)
            ->name('create');

        // Ver expediente (Policy)
        Route::livewire('/{expediente}', 'pages::expedientes.show')
            ->can('view', 'expediente')
            ->name('show');

        // Editar expediente (Policy)
        Route::livewire('/{expediente}/editar', 'pages::expedientes.edit')
            ->can('update', 'expediente')
            ->name('edit');

        // Revisión financiera (Jefe Financiero / Admin)
        Route::livewire('/{expediente}/revision', 'pages::expedientes.revision-financiera')
            ->can('revisarFinanciera', 'expediente')
            ->name('revision');

    });

    /*
    |----------------------------------------------------------------------
    | Reportes - Admin, Director, Jefe Financiero y Técnico
    |----------------------------------------------------------------------
    */
    Route::livewire('/reportes', 'pages::reportes.index')
        ->middleware('role:Administrador,Director General,Jefe Administrativo-Financiero,Técnico')
        ->name('reportes');

    /*
    |----------------------------------------------------------------------
    | Administración - Solo Administrador
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador')->group(function () {

        // Gestión de Usuarios
        Route::livewire('/usuarios', 'pages::admin.usuarios.index')->name('usuarios.index');
        Route::livewire('/usuarios/{usuario}', 'pages::admin.usuarios.show')->name('usuarios.show');

    });

    /*
    |----------------------------------------------------------------------
    | Bitácora - Administrador y Director General (solo lectura)
    |----------------------------------------------------------------------
    */
    Route::livewire('/admin/bitacora', 'pages::admin.bitacora.index')
        ->middleware('role:Administrador,Director General')
        ->name('admin.bitacora');

    /*
    |----------------------------------------------------------------------
    | Municipios - Administrador (CRUD) y Director General (solo lectura)
    |----------------------------------------------------------------------
    */
    Route::prefix('admin/municipios')->name('admin.municipios.')->middleware('role:Administrador,Director General')->group(function () {
        Route::livewire('/', 'pages::admin.municipios.index')->name('index');
        Route::livewire('/{municipio}', 'pages::admin.municipios.show')->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Gestión de Guías - Admin (CRUD), Director y Jefe Financiero (solo crear)
    |----------------------------------------------------------------------
    */
    Route::prefix('admin/guias')->name('admin.guias.')->middleware('role:Administrador,Director General,Jefe Administrativo-Financiero')->group(function () {
        Route::livewire('/', 'pages::admin.guias.index')->name('index');
        Route::livewire('/crear', 'pages::admin.guias.create')
            ->can('create', Guia::class)
            ->name('create');
        Route::livewire('/{guia}/editar', 'pages::admin.guias.edit')
            ->can('update', 'guia')
            ->name('edit');
    });

    /*
    |----------------------------------------------------------------------
    | Notificaciones - Historial de correos enviados
    |----------------------------------------------------------------------
    */
    Route::livewire('/admin/notificaciones', 'pages::admin.notificaciones.index')
        ->middleware('role:Administrador,Director General,Jefe Administrativo-Financiero,Técnico')
        ->name('admin.notificaciones.index');

});

/*
|--------------------------------------------------------------------------
| Archivos de rutas adicionales
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
