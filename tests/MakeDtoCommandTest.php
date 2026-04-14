<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Support\Facades\File;

test('it can generate a dto class', function () {
    $dtoPath = app_path('Dtos/TestDto.php');

    if (File::exists($dtoPath)) {
        File::delete($dtoPath);
    }

    $this->artisan('make:dto', ['name' => 'TestDto'])
        ->assertExitCode(0);

    expect(File::exists($dtoPath))->toBeTrue();

    $content = File::get($dtoPath);

    expect($content)->toContain('namespace App\Dtos;')
        ->and($content)->toContain('class TestDto extends Dto')
        ->and($content)->toContain('use Novius\LaravelDto\Dto;');
});
