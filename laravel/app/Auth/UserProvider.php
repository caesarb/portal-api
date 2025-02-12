<?php

namespace App\Auth;

use App\Clients\Portal;
use App\Models\User;
use App\Models\Role;
use Illuminate\Contracts\Auth\UserProvider as AuthUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserProvider implements AuthUserProvider {
    
    protected $portal;

    public function __construct(Portal $portal)
    {
        $this->portal = $portal;
    }


    public function retrieveByToken($identifier, $token){
        $response = $this->portal->get("/api/user")->response;
        if($response->status() != 200){
            session(['auth_error' => $response->getStatusCode()]);
            return null;
        }
        return new User($response->json());
    }

    public function retrieveByCredentials(array $credentials){
        return $this->retrieveByToken('uuid', request()->header('authorization'));
    }

    public function retrieveById($identifier) {}
    public function validateCredentials(Authenticatable $user, array $credentials){}
    public function updateRememberToken(Authenticatable $user, $token){}

}