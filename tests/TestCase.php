<?php

namespace Novius\LaravelDto\Tests;

use Novius\LaravelDto\DtoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DtoServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        //
    }
}
