<?php

namespace App\Providers;

use App\Auth\UserProvider;
use App\Clients\Portal;
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