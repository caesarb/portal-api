<?php

namespace Portal\Auth;

use Portal\Clients\PortalClient;
use Portal\Models\User;
use Illuminate\Contracts\Auth\UserProvider as AuthUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class UserProvider implements AuthUserProvider {
    
    protected $portal;

    public function __construct(PortalClient $portal)
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
        $authHeader = request()->header('authorization');
        if ($authHeader) {
            return $this->retrieveByToken('uuid', $authHeader);
        }

        // ligands and buster frontend work different:
        // check for api_token and add it as Bearer to the headers
        if (isset($credentials['api_token'])) {
            $token = 'Bearer ' . $credentials['api_token'];
            request()->headers->set('authorization', $token);
            return $this->retrieveByToken('uuid', $token);
        }

        return null;
    }

    public function retrieveById($identifier) {}
    public function validateCredentials(Authenticatable $user, array $credentials){}
    public function updateRememberToken(Authenticatable $user, $token){}
    
    /**
     * Determine if the user's password needs to be rehashed.
     *
     * @param  mixed  $user
     * @return bool
     */
    public function rehashPasswordIfRequired(
        Authenticatable $user,
        array $credentials,
        bool $force = false
    ): bool {
        return false;
    }
}