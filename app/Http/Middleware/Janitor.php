<?php

namespace Portal\Http\Middleware;

use Portal\Models\User;
use Portal\Models\Role;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;

class Janitor extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $access_keys = json_decode(env('EXTERNAL_ACCESS_KEYS'), true);
        foreach ($access_keys as $key => $value) {
            if ($request->hasHeader($key) && $request->header($key) === $value) {

                $syntheticUser = new User([
                    'username' => $key,
                    'role_id' => 2
                ]);
                Auth::setUser($syntheticUser);
                
                return $next($request);
            }
        }
        return $this->unauthenticated($request);
    }

    protected function unauthenticated($request, ...$guards)
    {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
