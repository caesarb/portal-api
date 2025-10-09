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
        $portalAuthConfig = require __DIR__ . '/../../config/auth.php';
        $existingAuthConfig = config('auth', []);

        config([
            'auth.guards' => array_merge($existingAuthConfig['guards'] ?? [], $portalAuthConfig['guards']),
            'auth.providers' => array_merge($existingAuthConfig['providers'] ?? [], $portalAuthConfig['providers']),
            'auth.defaults' => $portalAuthConfig['defaults'],
        ]);

        $router = $this->app->make(Router::class);

        $router->middlewareGroup('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            PortalAuthenticate::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $router->middlewareGroup('api.high', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.'2000,1',
            PortalAuthenticate::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $router->middlewareGroup('external_access', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            JanitorAuthenticate::class,
        ]);
        $router->middlewareGroup('external_access.high', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.'2000,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            JanitorAuthenticate::class,
        ]);
    }

    public function register() {}
}
