<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ChangeTenantSchemaFromDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        DB::purge('pgsql');

        $parsedHttpHost = explode(".", $request->getHost());

        if (count($parsedHttpHost) > 1) {
            $tenantName = $parsedHttpHost[0];
        }

        //dd($request->getRequestUri(), $request->getHost(), $tenantName);

        $tenant = \App\Models\Tenant::where('name', $tenantName)->first();

        if (! $tenant) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Invalid tenant.'], 400);
            }
            return abort(404);
        }

        $query = 'SET search_path TO ' . $tenant->name;

        app()['config']['database.connections.pgsql.search_path'] = $tenant->name;

        \Illuminate\Support\Facades\DB::connection('pgsql')->statement($query);

        return $next($request);
    }
}
