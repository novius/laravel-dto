<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Support\Fluent;
use Novius\LaravelDto\Dto;

test('it casts to Fluent', function () {
    $dto = new FluentCastDto(['data' => ['name' => 'John', 'age' => 30]]);

    expect($dto->data)->toBeInstanceOf(Fluent::class)
        ->and($dto->data->name)->toBe('John')
        ->and($dto->data->age)->toBe(30);
});

test('it accepts a Fluent instance', function () {
    $fluent = new Fluent(['name' => 'Jane']);
    $dto = new FluentCastDto(['data' => $fluent]);

    expect($dto->data)->toBe($fluent)
        ->and($dto->data->name)->toBe('Jane');
});

test('it handles nested DTOs even when typed with another DTO class', function () {
    $nestedData = ['title' => 'Sample Post'];
    $dto = new ParentDto(['post' => $nestedData]);

    expect($dto->post)->toBeInstanceOf(ChildDto::class)
        ->and($dto->post->title)->toBe('Sample Post');
});

test('it converts Fluent back to array in toArray', function () {
    $dto = new FluentCastDto(['data' => ['name' => 'John']]);
    $array = $dto->toArray();

    expect($array['data'])->toBeArray()
        ->and($array['data']['name'])->toBe('John');
});

test('it handles other classes when provided as instance', function () {
    $stdClass = new \stdClass;
    $stdClass->foo = 'bar';

    $dto = new class(['obj' => $stdClass]) extends Dto
    {
        public \stdClass $obj;
    };

    expect($dto->obj)->toBe($stdClass);
});

/**
 * @property Fluent $data
 */
class FluentCastDto extends Dto
{
    protected Fluent $data;
}

/**
 * @property string $title
 */
class ChildDto extends Dto
{
    protected string $title;
}

/**
 * @property ChildDto $post
 */
class ParentDto extends Dto
{
    protected ChildDto $post;
}
