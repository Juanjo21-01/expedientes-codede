<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');


    /*
    |----------------------------------------------------------------------
    | Guías - Todos pueden ver las guías activas
    |----------------------------------------------------------------------
    */
    Route::livewire('/guias', 'pages::guia.index')->name('guias');

    /*
    |----------------------------------------------------------------------
    | Municipios (Técnico y Municipal) - Solo sus municipios asignados - Admin, Director pueden ver todos 
    |----------------------------------------------------------------------
    */
    Route::prefix('municipios')->name('municipios.')->middleware('role:Administrador,Director General,Jefe Administrativo-Financiero,Técnico,Municipal')->group(function () {
        Route::livewire('/', 'pages::municipios.index')
            ->can('viewAny', Municipio::class)
            ->name('index');

        Route::livewire('/{municipio}', 'pages::municipios.show')
            ->can('view', 'municipio')
            ->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Notificaciones - Historial para roles Municipal y Técnico
    |----------------------------------------------------------------------
    */
    Route::livewire('/notificaciones', 'pages::notificaciones.index')
        ->middleware('role:Municipal,Técnico')
        ->name('notificaciones.index');

    /*
    |----------------------------------------------------------------------
    | Perfil - Todos pueden ver/editar su perfil
    |----------------------------------------------------------------------
    */
    Route::get('/perfil', fn () => redirect()->route('profile.edit'))->name('perfil');

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
    | Usuarios - Admin (CRUD) y Director General (solo lectura)
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador,Director General')->group(function () {
        Route::livewire('/usuarios', 'pages::admin.usuarios.index')
            ->can('viewAny', User::class)
            ->name('usuarios.index');

        Route::livewire('/usuarios/{usuario}', 'pages::admin.usuarios.show')
            ->can('view', 'usuario')
            ->name('usuarios.show');
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
    | Municipios - Administrador (CRUD), Director y Jefe Financiero (solo lectura)
    |----------------------------------------------------------------------
    */
    Route::prefix('admin/municipios')->name('admin.municipios.')->middleware('role:Administrador,Director General,Jefe Administrativo-Financiero')->group(function () {
        Route::livewire('/', 'pages::admin.municipios.index')
            ->can('viewAny', Municipio::class)
            ->name('index');

        Route::livewire('/{municipio}', 'pages::admin.municipios.show')
            ->can('view', 'municipio')
            ->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Gestión de Guías - Admin (CRUD), Director y Jefe Financiero (crear y editar propias)
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
        ->middleware('role:Administrador,Director General,Jefe Administrativo-Financiero')
        ->name('admin.notificaciones.index');

    /*
    |----------------------------------------------------------------------
    | Utilidad Técnica - Generar enlace simbólico de storage (solo Admin)
    |----------------------------------------------------------------------
    */
    Route::get('/admin/storage-link', function () {
        Artisan::call('storage:link');

        return response(
            trim(Artisan::output()) ?: 'Comando storage:link ejecutado.',
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    })->middleware('role:Administrador')->name('admin.storage-link');

});

/*
|--------------------------------------------------------------------------
| Archivos de rutas adicionales
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
