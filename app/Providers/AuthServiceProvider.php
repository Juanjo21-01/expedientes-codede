<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Expediente;
use App\Models\User;
use App\Models\Municipio;
use App\Models\Guia;
use App\Policies\ExpedientePolicy;
use App\Policies\UserPolicy;
use App\Policies\MunicipioPolicy;
use App\Policies\GuiaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Expediente::class => ExpedientePolicy::class,
        User::class => UserPolicy::class,
        Municipio::class => MunicipioPolicy::class,
        Guia::class => GuiaPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
