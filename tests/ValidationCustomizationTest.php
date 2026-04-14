<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Validation\ValidationException;
use Novius\LaravelDto\Dto;
use Orchestra\Testbench\TestCase;

class ValidationCustomizationTest extends TestCase
{
    /** @test */
    public function it_can_customize_validation_messages()
    {
        $this->expectException(ValidationException::class);

        try {
            new CustomValidationDto(['name' => 'Jo']);
        } catch (ValidationException $e) {
            $this->assertEquals('The name is too short!', $e->validator->errors()->first('name'));
            throw $e;
        }
    }

    /** @test */
    public function it_can_customize_validation_attributes()
    {
        $this->expectException(ValidationException::class);

        try {
            new CustomValidationDto(['email' => 'invalid-email']);
        } catch (ValidationException $e) {
            // "The user email field must be a valid email address." (default Laravel message)
            // But with 'user email' as attribute name instead of 'email'
            $this->assertStringContainsString('user email', $e->validator->errors()->first('email'));
            throw $e;
        }
    }
}

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
