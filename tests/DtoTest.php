<?php

namespace Novius\LaravelDto\Tests;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Novius\LaravelDto\Dto;
use Orchestra\Testbench\TestCase;

class DtoTest extends TestCase
{
    /** @test */
    public function it_can_create_a_dto_with_attributes()
    {
        $dto = new UserDto(['name' => 'John Doe', 'age' => 30]);

        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals(30, $dto->age);
    }

    /** @test */
    public function it_throws_exception_if_property_does_not_exist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'email' does not exist");

        new UserDto(['email' => 'john@example.com']);
    }

    /** @test */
    public function it_applies_default_values()
    {
        $dto = new UserDto(['name' => 'John Doe']);

        $this->assertEquals(18, $dto->age);
    }

    /** @test */
    public function it_validates_properties_on_constructor()
    {
        $this->expectException(ValidationException::class);

        new UserDto(['name' => 'J', 'age' => 30]); // Name too short
    }

    /** @test */
    public function it_casts_properties()
    {
        $dto = new UserDto(['name' => 'John Doe', 'age' => '25']);

        $this->assertSame(25, $dto->age);
        $this->assertIsInt($dto->age);
    }

    /** @test */
    public function it_works_with_magic_getters()
    {
        $dto = new UserDto(['name' => 'John Doe', 'age' => 30]);

        $this->assertEquals('John Doe', $dto->getName());
        $this->assertEquals(30, $dto->getAge());
    }

    /** @test */
    public function it_works_with_magic_setters_and_validates()
    {
        $dto = new UserDto(['name' => 'John Doe', 'age' => 30]);

        $dto->setName('Jane Doe');
        $this->assertEquals('Jane Doe', $dto->name);

        $this->expectException(ValidationException::class);
        $dto->setName('J');
    }

    /** @test */
    public function it_can_convert_to_array()
    {
        $dto = new UserDto(['name' => 'John Doe', 'age' => '30']);

        $expected = [
            'name' => 'John Doe',
            'age' => 30,
        ];

        $this->assertEquals($expected, $dto->toArray());
    }
}

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
