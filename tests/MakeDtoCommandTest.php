<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Support\Facades\File;
use Novius\LaravelDto\DtoServiceProvider;
use Orchestra\Testbench\TestCase;

class MakeDtoCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            DtoServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_generate_a_dto_class()
    {
        $dtoPath = app_path('Dtos/TestDto.php');

        if (File::exists($dtoPath)) {
            File::delete($dtoPath);
        }

        $this->artisan('make:dto', ['name' => 'TestDto'])
            ->assertExitCode(0);

        $this->assertTrue(File::exists($dtoPath));

        $content = File::get($dtoPath);

        $this->assertStringContainsString('namespace App\Dtos;', $content);
        $this->assertStringContainsString('class TestDto extends Dto', $content);
        $this->assertStringContainsString('use Novius\LaravelDto\Dto;', $content);
    }
}
