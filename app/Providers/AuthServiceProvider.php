<?php

namespace Portal\Providers;

use Portal\Auth\UserProvider;
use Portal\Clients\Portal;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as IlluminateAuthServiceProvider;
use Auth;

class AuthServiceProvider extends IlluminateAuthServiceProvider
{

    protected $policies = [];

    public function boot()
    {
        Auth::provider('portal', function ($app, array $config) {
            return new UserProvider(new Portal());
        });
    }
}