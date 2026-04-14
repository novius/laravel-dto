<?php

namespace Novius\LaravelDto;

use Illuminate\Support\ServiceProvider;
use Novius\LaravelDto\Console\DtoMakeCommand;

class DtoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DtoMakeCommand::class,
            ]);
        }
    }
}
