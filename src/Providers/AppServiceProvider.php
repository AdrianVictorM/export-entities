<?php

namespace adrianvm\ExportModelConstants\Providers;

use Illuminate\Support\ServiceProvider;
use adrianvm\ExportModelConstants\Console\Commands\ExportModelConstantsCommand;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the command
        $this->commands([
            ExportModelConstantsCommand::class,
        ]);
    }

    public function boot()
    {
        // Package boot logic if necessary
    }
}
