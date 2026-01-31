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
    | Guía - Todos pueden ver la guía actual
    |----------------------------------------------------------------------
    */
    Route::livewire('/guia', 'pages::guia.show')->name('guia');

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

        // Crear expediente (solo Técnico)
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

        // Revisión financiera (solo Jefe Financiero)
        Route::livewire('/{expediente}/revision', 'pages::expedientes.revision-financiera')
            ->can('revisarFinanciera', 'expediente')
            ->name('revision');

    });

    /*
    |----------------------------------------------------------------------
    | Reportes - Solo Director General y Jefe Financiero
    |----------------------------------------------------------------------
    */
    Route::livewire('/reportes', 'pages::reportes.index')
        ->middleware('role:Director General,Jefe Administrativo-Financiero')
        ->name('reportes');

    /*
    |----------------------------------------------------------------------
    | Administración - Solo Administrador
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador')->group(function () {

        // Gestión de Usuarios
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::livewire('/', 'pages::admin.usuarios.index')->name('index');
            Route::livewire('/crear', 'pages::admin.usuarios.create')->name('create');
            Route::livewire('/{user}/editar', 'pages::admin.usuarios.edit')->name('edit');
        });

        // Gestión de Municipios
        Route::prefix('municipios')->name('municipios.')->group(function () {
            Route::livewire('/', 'pages::admin.municipios.index')->name('index');
            Route::livewire('/crear', 'pages::admin.municipios.create')->name('create');
            Route::livewire('/{municipio}/editar', 'pages::admin.municipios.edit')->name('edit');
        });

        // Gestión de Guías
        Route::prefix('guias')->name('guias.')->group(function () {
            Route::livewire('/', 'pages::admin.guias.index')->name('index');
            Route::livewire('/crear', 'pages::admin.guias.create')->name('create');
            Route::livewire('/{guia}/editar', 'pages::admin.guias.edit')->name('edit');
        });

        // Bitácora (solo lectura)
        Route::livewire('/bitacora', 'pages::admin.bitacora.index')->name('bitacora');

    });

});

/*
|--------------------------------------------------------------------------
| Archivos de rutas adicionales
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
