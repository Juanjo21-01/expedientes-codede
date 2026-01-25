<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\MunicipioAsignadoMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\UsuarioActivoMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        //  Registrar el middleware de usuario activo personalizado
        $middleware->append(UsuarioActivoMiddleware::class);

        // Registrar el middleware de rol personalizado
        $middleware->alias([
            'role' => App\Http\Middleware\RoleMiddleware::class,
            'usuario_activo' => \App\Http\Middleware\UsuarioActivoMiddleware::class,
            'municipio_asignado' => App\Http\Middleware\MunicipioAsignadoMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
