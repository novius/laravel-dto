<?php

namespace Novius\LaravelDto\Tests;

use Novius\LaravelDto\Dto;

enum UserStatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

enum UserTypeEnum: int
{
    case Admin = 1;
    case User = 2;
}

test('it casts to backed enum string', function () {
    $dto = new EnumCastDto(['status' => 'active']);
    expect($dto->status)->toBe(UserStatusEnum::Active);
});

test('it casts to backed enum int', function () {
    $dto = new EnumCastDto(['type' => 1]);
    expect($dto->type)->toBe(UserTypeEnum::Admin);
});

test('it accepts an enum instance', function () {
    $dto = new EnumCastDto(['status' => UserStatusEnum::Inactive]);
    expect($dto->status)->toBe(UserStatusEnum::Inactive);
});

test('it can convert to array with enums', function () {
    $dto = new EnumCastDto([
        'status' => 'inactive',
        'type' => 2,
    ]);

    $array = $dto->toArray();

    expect($array['status'])->toBe('inactive')
        ->and($array['type'])->toBe(2);
});

/**
 * @property UserStatusEnum $status
 * @property UserTypeEnum $type
 */
class EnumCastDto extends Dto
{
    protected UserStatusEnum $status;

    protected UserTypeEnum $type;

    protected function casts(): array
    {
        return [
            'status' => UserStatusEnum::class,
            'type' => UserTypeEnum::class,
        ];
    }
}
