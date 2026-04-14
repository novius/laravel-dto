<?php

namespace Novius\LaravelDto\Console;

use Illuminate\Console\GeneratorCommand;

class DtoMakeCommand extends GeneratorCommand
{
    protected $name = 'make:dto';

    protected $description = 'Create a new DTO class';

    protected $type = 'Dto';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/dto.stub';
    }

    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Dtos';
    }
}
