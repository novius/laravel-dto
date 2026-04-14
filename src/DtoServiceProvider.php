<?php

namespace Novius\LaravelDto;

use Illuminate\Support\ServiceProvider;
use Novius\LaravelDto\Console\DtoMakeCommand;

class DtoServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                DtoMakeCommand::class,
            ]);
        }
    }
}
