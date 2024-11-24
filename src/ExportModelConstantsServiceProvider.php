<?php

namespace Adrianvm\ExportModelConstants;

use Illuminate\Support\ServiceProvider;
use Adrianvm\ExportModelConstants\Console\Commands\ExportModelConstantsCommand;

class ExportModelConstantsServiceProvider extends ServiceProvider
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
