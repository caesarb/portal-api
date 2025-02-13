<?php

namespace Portal\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        $guards = empty($guards) ? [null] : $guards;
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->guest()) {
                $status = session('auth_error', 401);
                session()->forget('auth_error');

                return response()->json(['message' => 'Unauthorized'], $status);
            }
        }

        return $next($request);
    }

    protected function unauthenticated($request, ...$guards)
    {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
