<?php

namespace Portal\Providers;

use Portal\Auth\UserProvider;
use Portal\Clients\PortalClient;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as IlluminateAuthServiceProvider;
use Auth;

class AuthServiceProvider extends IlluminateAuthServiceProvider
{

    protected $policies = [];

    public function register()
    {
        $this->app->singleton(PortalClient::class, function ($app) {
            return new PortalClient();
        });
        Auth::provider('portal', function ($app) {
            return new UserProvider($app->make(PortalClient::class));
        });
    }

    public function boot()
    {
    }
}