<?php

namespace App\Providers;

use App\Http\Middleware\ChangeTenantSchemaFromDomain;
use App\Http\Middleware\ChangeTenantSchemaFromUri;
use App\Http\Middleware\PreventAccessFromDomain;
use Illuminate\Support\ServiceProvider;

class TenantSchemaProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $tenancyMiddlwares = [
            // Highest prority
            PreventAccessFromDomain::class,

            ChangeTenantSchemaFromDomain::class,
            ChangeTenantSchemaFromUri::class,
        ];

        foreach (array_reverse($tenancyMiddlwares) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
