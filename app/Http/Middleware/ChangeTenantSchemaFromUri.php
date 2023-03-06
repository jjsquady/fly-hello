<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ChangeTenantSchemaFromUri
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cleanUri = Str::replaceFirst("/api", "", $request->getRequestUri());

        $tenantName = explode("/", Str::replaceFirst("/", "",$cleanUri));

        //dd($request->getRequestUri(), $request->getHttpHost());

        $tenant = \App\Models\Tenant::where('name', $tenantName[0])->first();

        if (! $tenant) {
            return response()->json(['error' => 'Invalid tenant.'], 400);
        }

        $query = 'SET search_path TO ' . $tenant->name;
        \Illuminate\Support\Facades\DB::connection('pgsql')->statement($query);

        return $next($request);
    }
}
