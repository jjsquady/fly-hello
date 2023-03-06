<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\DB;

class TenantMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {--schema=} {--install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates a new schema to Tenant';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::purge('pgsql');

        $schema = $this->option('schema') ?? 'public';

        DB::connection('pgsql')->statement("SET search_path TO {$schema}");

        if ($schema != "public" && $this->option('install')) {
            $this->call("migrate:install");
        }

        $this->call("migrate", ['--force' => true]);

        if ($schema == 'public') {
            $this->call('migrate', ['--path' => 'database/migrations/landlord']);
        }
    }
}
