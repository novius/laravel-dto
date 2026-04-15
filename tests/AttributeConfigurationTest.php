<?php

namespace Novius\LaravelDto\Tests;

use DateTimeInterface;
use Illuminate\Validation\ValidationException;
use Novius\LaravelDto\Attributes\Cast;
use Novius\LaravelDto\Attributes\DefaultValue;
use Novius\LaravelDto\Attributes\Rules;
use Novius\LaravelDto\Dto;

test('it can use Rules attribute', function () {
    $dto = new AttributeConfigDto(['name' => 'Jo']); // Name too short
    $dto->validate();
})->throws(ValidationException::class);

test('it can use DefaultValue attribute', function () {
    $dto = new AttributeConfigDto(['name' => 'John Doe']);
    expect($dto->age)->toBe(20);
});

test('it can use Cast attribute', function () {
    $dto = new AttributeConfigDto(['name' => 'John Doe', 'is_admin' => '1']);
    expect($dto->is_admin)->toBeTrue();
});

test('it uses Cast attribute for DateTimeInterface in toArray', function () {
    $date = '2023-01-01 12:00:00';
    $dto = new DateTimeCastDto(['date' => $date]);

    $array = $dto->toArray();

    // The Cast attribute #[Cast('date:Y-m-d')] should be respected in toArray()
    expect($array['date'])->toBe('2023-01-01');
});

test('methods have priority over attributes', function () {
    $dto = new OverriddenAttributeDto;
    // DefaultValue attribute says 'attr-default', but defaults() method says 'method-default'
    expect($dto->prop)->toBe('method-default');
});

class AttributeConfigDto extends Dto
{
    #[Rules('required|min:3')]
    protected string $name;

    #[DefaultValue(20)]
    protected int $age;

    #[Cast('bool')]
    protected bool $is_admin;
}

class DateTimeCastDto extends Dto
{
    #[Cast('date:Y-m-d')]
    protected DateTimeInterface $date;
}

class OverriddenAttributeDto extends Dto
{
    #[DefaultValue('attr-default')]
    protected string $prop;

    protected function defaults(): array
    {
        return [
            'prop' => 'method-default',
        ];
    }
}
