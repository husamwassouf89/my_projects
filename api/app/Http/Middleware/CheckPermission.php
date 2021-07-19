<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next, ...$permissions)
    {

        $state = Auth::user()->hasOnePermissionAtLeast($permissions);

        if(!$state)
        {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }
        return $next($request);
    }
}
