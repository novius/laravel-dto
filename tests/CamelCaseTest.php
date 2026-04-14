<?php

namespace Novius\LaravelDto\Tests;

use Novius\LaravelDto\Dto;

test('it can access snake case properties via camel case getters', function () {
    $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

    expect($dto->getFirstName())->toBe('John')
        ->and($dto->getLastName())->toBe('Doe');
});

test('it can set snake case properties via camel case setters', function () {
    $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

    $dto->setFirstName('Jane');
    expect($dto->first_name)->toBe('Jane')
        ->and($dto->getFirstName())->toBe('Jane');
});

test('it can access snake case properties via camel case magic get', function () {
    $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

    expect($dto->firstName)->toBe('John')
        ->and($dto->lastName)->toBe('Doe');
});

test('it can set snake case properties via camel case magic set', function () {
    $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

    $dto->firstName = 'Jane';
    expect($dto->first_name)->toBe('Jane')
        ->and($dto->firstName)->toBe('Jane');
});

test('it can check snake case properties via camel case magic isset', function () {
    $dto = new SnakeDto(['first_name' => 'John']);

    expect(isset($dto->firstName))->toBeTrue()
        ->and(isset($dto->lastName))->toBeFalse();
});

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $firstName
 * @property string $lastName
 *
 * @method string getFirstName()
 * @method string getLastName()
 * @method self setFirstName(string $value)
 * @method self setLastName(string $value)
 */
class SnakeDto extends Dto
{
    protected string $first_name;

    protected string $last_name;

    protected function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'string',
        ];
    }
}
