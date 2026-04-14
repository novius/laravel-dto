<?php

namespace Novius\LaravelDto\Tests;

use Carbon\Carbon;
use Novius\LaravelDto\Dto;

test('it infers int from php type', function () {
    $dto = new NativeTypeDto(['age' => '30']);
    expect($dto->age)->toBe(30)->toBeInt();
});

test('it infers bool from php type', function () {
    $dto = new NativeTypeDto(['is_admin' => 'yes']);
    expect($dto->is_admin)->toBeTrue();
});

test('it infers float from php type', function () {
    $dto = new NativeTypeDto(['price' => '12.5']);
    expect($dto->price)->toBe(12.5)->toBeFloat();
});

test('it infers Carbon from php type', function () {
    $dto = new NativeTypeDto(['created_at' => '2024-04-14 12:00:00']);
    expect($dto->created_at)->toBeInstanceOf(Carbon::class);
});

test('it infers Enum from php type', function () {
    $dto = new NativeTypeDto(['status' => 'active']);
    expect($dto->status)->toBe(UserStatus::active);
});

test('it infers array from php type', function () {
    $dto = new NativeTypeDto(['tags' => ['tag1', 'tag2']]);
    expect($dto->tags)->toBe(['tag1', 'tag2']);
});

test('it infers DTO from php type', function () {
    $dto = new NativeTypeDto(['sub_dto' => ['name' => 'Sub']]);
    expect($dto->sub_dto)->toBeInstanceOf(SubDto::class)
        ->and($dto->sub_dto->name)->toBe('Sub');
});

test('it respects explicit casts over native type', function () {
    $dto = new NativeTypeDto(['explicit_cast' => '12.3456']);
    // Explicit cast is decimal:2
    expect($dto->explicit_cast)->toBe('12.35');
});

test('toArray output is consistent with native types', function () {
    $dto = new NativeTypeDto([
        'age' => '30',
        'is_admin' => '1',
        'created_at' => '2024-04-14 12:00:00',
        'status' => 'active',
        'sub_dto' => ['name' => 'Sub'],
    ]);

    $array = $dto->toArray();

    expect($array['age'])->toBe(30)
        ->and($array['is_admin'])->toBeTrue()
        ->and($array['created_at'])->toBe('2024-04-14 12:00:00')
        ->and($array['status'])->toBe('active')
        ->and($array['sub_dto'])->toBe(['name' => 'Sub']);
});

/**
 * @property int $age
 * @property bool $is_admin
 * @property float $price
 * @property Carbon $created_at
 * @property UserStatus $status
 * @property array $tags
 * @property SubDto $sub_dto
 * @property mixed $explicit_cast
 */
class NativeTypeDto extends Dto
{
    protected int $age;

    protected bool $is_admin;

    protected float $price;

    protected Carbon $created_at;

    protected UserStatus $status;

    protected array $tags;

    protected SubDto $sub_dto;

    protected mixed $explicit_cast;

    protected function casts(): array
    {
        return [
            'explicit_cast' => 'decimal:2',
        ];
    }
}

/**
 * @property string $name
 */
class SubDto extends Dto
{
    protected string $name;
}

enum UserStatus: string
{
    case active = 'active';
    case inactive = 'inactive';
}
