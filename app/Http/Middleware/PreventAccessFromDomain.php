<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Response;

class PreventAccessFromDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $parsedHttpHost = explode(".", $request->getHost());

        if (count($parsedHttpHost) > 1) {
            $tenantName = $parsedHttpHost[0];
        }

        //dd($request->getRequestUri(), $request->getHost(), $tenantName);

        $tenant = \App\Models\Tenant::where('name', $tenantName)->first();

        if ($tenant) {
            throw new Exception("Cannot access this route from a domain.");
        }

        return $next($request);
    }
}
