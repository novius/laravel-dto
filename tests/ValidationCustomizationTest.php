<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Validation\ValidationException;
use Novius\LaravelDto\Dto;

test('it can customize validation messages', function () {
    try {
        new CustomValidationDto(['name' => 'Jo']);
    } catch (ValidationException $e) {
        expect($e->validator->errors()->first('name'))->toBe('The name is too short!');
        throw $e;
    }
})->throws(ValidationException::class);

test('it can customize validation attributes', function () {
    try {
        new CustomValidationDto(['email' => 'invalid-email']);
    } catch (ValidationException $e) {
        // "The user email field must be a valid email address." (default Laravel message)
        // But with 'user email' as attribute name instead of 'email'
        expect($e->validator->errors()->first('email'))->toContain('user email');
        throw $e;
    }
})->throws(ValidationException::class);

class CustomValidationDto extends Dto
{
    protected string $name;

    protected string $email;

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.min' => 'The :attribute is too short!',
        ];
    }

    protected function attributes(): array
    {
        return [
            'email' => 'user email',
        ];
    }
}
