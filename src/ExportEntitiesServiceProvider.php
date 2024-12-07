<?php

namespace Adrianvm\ExportEntities;

use Illuminate\Support\ServiceProvider;
use Adrianvm\ExportEntities\Console\Commands\ExportConstantsCommand;
use Adrianvm\ExportEntities\Console\Commands\ExportEnumsCommand;

class ExportEntitiesServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the command
        $this->commands([
            ExportEnumsCommand::class,
            ExportConstantsCommand::class,
        ]);
    }

    public function boot()
    {
        // Package boot logic if necessary
    }
}
