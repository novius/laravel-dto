<?php

namespace Novius\LaravelDto\Tests;

use Novius\LaravelDto\Attributes\Map;
use Novius\LaravelDto\Dto;

test('it maps properties in toArray using map method', function () {
    $dto = new class(['date_begin' => '2024-01-01']) extends Dto
    {
        protected string $date_begin;

        protected function map(): array
        {
            return [
                'date_begin' => 'dt-debut',
            ];
        }
    };

    $array = $dto->toArray();

    expect($array)->toHaveKey('dt-debut')
        ->and($array['dt-debut'])->toBe('2024-01-01')
        ->and($array)->not->toHaveKey('date_begin');
});

test('it maps properties in toArray using Map attribute', function () {
    $dto = new class(['date_end' => '2024-12-31']) extends Dto
    {
        #[Map('dt-fin')]
        protected string $date_end;
    };

    $array = $dto->toArray();

    expect($array)->toHaveKey('dt-fin')
        ->and($array['dt-fin'])->toBe('2024-12-31')
        ->and($array)->not->toHaveKey('date_end');
});

test('map method takes precedence over Map attribute', function () {
    $dto = new class(['name' => 'John']) extends Dto
    {
        #[Map('attribute-name')]
        protected string $name;

        protected function map(): array
        {
            return [
                'name' => 'method-name',
            ];
        }
    };

    $array = $dto->toArray();

    expect($array)->toHaveKey('method-name')
        ->and($array['method-name'])->toBe('John')
        ->and($array)->not->toHaveKey('attribute-name')
        ->and($array)->not->toHaveKey('name');
});
