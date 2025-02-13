<?php

namespace Portal\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Portal\Http\Middleware\Authenticate as PortalAuthenticate;
use Portal\Http\Middleware\Janitor as JanitorAuthenticate;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $router = $this->app->make(Router::class);

        $router->middlewareGroup('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            PortalAuthenticate::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $router->middlewareGroup('external_access', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            JanitorAuthenticate::class,
        ]);
    }

    public function register() {}
}
