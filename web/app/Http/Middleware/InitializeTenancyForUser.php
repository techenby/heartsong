<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyForUser
{
    /**
     * Switch the request into the authenticated user's private database.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        tenancy()->initialize($request->user()->provisionTenant());

        return $next($request);
    }
}
