<?php

namespace Novius\LaravelDto\Tests;

use InvalidArgumentException;
use Novius\LaravelDto\Attributes\ExcludeFromDTO;
use Novius\LaravelDto\Dto;

test('it cannot set excluded property via constructor', function () {
    new FullExcludeDto(['name' => 'John', 'password' => 'secret']);
})->throws(InvalidArgumentException::class, "Property 'password' is excluded from DTO");

test('it cannot set excluded property via magic setter', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    $dto->password = 'secret';
})->throws(InvalidArgumentException::class, "Property 'password' is excluded from DTO");

test('it cannot set excluded property via magic method setter', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    $dto->setPassword('secret');
})->throws(InvalidArgumentException::class, "Property 'password' is excluded from DTO");

test('it cannot set excluded property via fluent setter', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    $dto->password('secret');
})->throws(InvalidArgumentException::class, 'Method password not found');

test('it cannot access excluded property via magic getter', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    $val = $dto->password;
})->throws(InvalidArgumentException::class, "Property 'password' does not exist");

test('it cannot access excluded property via magic method getter', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    $val = $dto->getPassword();
})->throws(InvalidArgumentException::class, "Property 'password' does not exist");

test('it cannot access excluded property via fluent getter', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    $val = $dto->password();
})->throws(InvalidArgumentException::class, 'Method password not found');

test('isset returns false for excluded property', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    expect(isset($dto->password))->toBeFalse();
});

test('excluded property is not in toArray', function () {
    $dto = new FullExcludeDto(['name' => 'John']);
    // On force l'initialisation de la propriété protégée pour le test via Reflection si nécessaire,
    // mais ici on veut surtout vérifier que même si elle avait une valeur par défaut elle serait ignorée.
    expect($dto->toArray())->toBe(['name' => 'John']);
});

class FullExcludeDto extends Dto
{
    protected string $name;

    #[ExcludeFromDTO]
    protected string $password;
}
