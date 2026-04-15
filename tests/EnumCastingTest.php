<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Validation\ValidationException;
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

test('it passes validation when property is an enum', function () {
    $dto = new EnumCastDto(['status' => UserStatusEnum::Active, 'type' => UserTypeEnum::Admin]);
    $dto->validate();
    expect($dto->status)->toBe(UserStatusEnum::Active);
});

class NestedDtoToValidate extends Dto
{
    public string $name;

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3',
        ];
    }
}

class ParentDtoToValidate extends Dto
{
    public NestedDtoToValidate $child;

    protected function rules(): array
    {
        return [
            'child' => 'required',
        ];
    }
}

test('it validates nested DTOs', function () {
    $child = new NestedDtoToValidate(['name' => 'Jo']); // Too short
    $parent = new ParentDtoToValidate(['child' => $child]);

    $parent->validate();
})->throws(ValidationException::class);

/**
 * @property UserStatusEnum $status
 * @property UserTypeEnum $type
 */
class EnumCastDto extends Dto
{
    protected UserStatusEnum $status;

    protected UserTypeEnum $type;

    protected function rules(): array
    {
        return [
            'status' => 'required',
            'type' => 'required',
        ];
    }

    protected function casts(): array
    {
        return [
            'status' => UserStatusEnum::class,
            'type' => UserTypeEnum::class,
        ];
    }
}
