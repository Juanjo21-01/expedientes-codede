<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Guia\Show as GuiaShow;
use App\Http\Livewire\Expedientes\Index as ExpedientesIndex;
use App\Http\Livewire\Expedientes\Create as ExpedientesCreate;
use App\Http\Livewire\Expedientes\Show as ExpedientesShow;
// use App\Http\Livewire\Admin\Usuarios\Index as UsuariosIndex;
use App\Models\User;
use App\Http\Livewire\Admin\Municipios\Index as MunicipiosIndex;
use App\Http\Livewire\Admin\Guias\Index as GuiasIndex;
use App\Http\Livewire\Reportes\Index as ReportesIndex;
use App\Http\Livewire\Bitacora\Index as BitacoraIndex;

// Ruta pública
Route::get('/', function () {
    return view('auth.login');
})->name('inicio');

// Rutas privadas -> Grupo autenticado
Route::middleware(['auth', 'usuario_activo'])->group(function () {
    // Redireccionar al dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Dashboard (todos)
    // Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::view('dashboard', 'pages.dashboard')->name('dashboard');

    // Solo administrador
    Route::middleware('role:Administrador')->name('admin.')->group(function () {

        // Usuarios
        Route::livewire('/usuarios', 'pages::admin.usuarios.index')
        ->can('viewAny', User::class)
        ->name('usuarios.index');
        // Route::get('/usuarios', UsuariosIndex::class)->can('viewAny', \App\Models\User::class)->name('usuarios.index');
        // create/edit con can('create/update', User::class)

        // Route::get('/municipios', MunicipiosIndex::class)->can('viewAny', \App\Models\Municipio::class)->name('municipios.index');

        // Route::get('/guias', GuiasIndex::class)->can('viewAny', \App\Models\Guia::class)->name('guias.index');

        // Route::get('/bitacora', BitacoraIndex::class)->name('bitacora');
    });

    // Guía (todos)
    // Route::get('/guia', GuiaShow::class)->name('guia');

    // Expedientes
    Route::middleware('municipio_asignado')->group(function () {
        // Route::get('/expedientes', ExpedientesIndex::class)->name('expedientes.index');
        // Route::get('/expedientes/crear', ExpedientesCreate::class)->can('create', \App\Models\Expediente::class)->name('expedientes.create');
        // Route::get('/expedientes/{expediente}', ExpedientesShow::class)->can('view', [\App\Models\Expediente::class, 'expediente'])->name('expedientes.show');
        // Agregaremos edit en Show o separate
    });

    // Reportes (Director + Jefe)
    Route::middleware('role:Director,Jefe Administrativo-Financiero')->group(function () {
        // Route::get('/reportes', ReportesIndex::class)->name('reportes');
    });
});

require __DIR__.'/settings.php';
