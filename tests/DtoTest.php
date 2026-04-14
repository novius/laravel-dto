<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Novius\LaravelDto\Dto;

test('it can create a dto with attributes', function () {
    $dto = new UserDto(['name' => 'John Doe', 'age' => 30]);

    expect($dto->name)->toBe('John Doe')
        ->and($dto->age)->toBe(30);
});

test('it throws exception if property does not exist', function () {
    new UserDto(['email' => 'john@example.com']);
})->throws(InvalidArgumentException::class, "Property 'email' does not exist");

test('it applies default values', function () {
    $dto = new UserDto(['name' => 'John Doe']);

    expect($dto->age)->toBe(18);
});

test('it validates properties', function () {
    $dto = new UserDto(['name' => 'J', 'age' => 30]); // Name too short
    $dto->validate();
})->throws(ValidationException::class);

test('it casts properties', function () {
    $dto = new UserDto(['name' => 'John Doe', 'age' => '25']);

    expect($dto->age)->toBe(25)
        ->and($dto->age)->toBeInt();
});

test('it works with magic getters', function () {
    $dto = new UserDto(['name' => 'John Doe', 'age' => 30]);

    expect($dto->getName())->toBe('John Doe')
        ->and($dto->getAge())->toBe(30);
});

test('it works with magic setters', function () {
    $dto = new UserDto(['name' => 'John Doe', 'age' => 30]);

    $dto->setName('Jane Doe');
    expect($dto->name)->toBe('Jane Doe');

    $dto->setName('J');
    $dto->validate();
})->throws(ValidationException::class);

test('it can convert to array', function () {
    $dto = new UserDto(['name' => 'John Doe', 'age' => '30']);

    $expected = [
        'name' => 'John Doe',
        'age' => 30,
    ];

    expect($dto->toArray())->toBe($expected);
});

/**
 * @property string $name
 * @property int $age
 *
 * @method string getName()
 * @method int getAge()
 * @method self setName(string $name)
 */
class UserDto extends Dto
{
    protected string $name;

    protected int $age;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'age' => 'required|integer|min:0',
        ];
    }

    protected function defaults(): array
    {
        return [
            'age' => 18,
        ];
    }

    protected function casts(): array
    {
        return [
            'age' => 'int',
        ];
    }
}
