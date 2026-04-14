<?php

namespace Novius\LaravelDto\Tests;

use Novius\LaravelDto\Dto;
use Orchestra\Testbench\TestCase;

class CamelCaseTest extends TestCase
{
    /** @test */
    public function it_can_access_snake_case_properties_via_camel_case_getters()
    {
        $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

        $this->assertEquals('John', $dto->getFirstName());
        $this->assertEquals('Doe', $dto->getLastName());
    }

    /** @test */
    public function it_can_set_snake_case_properties_via_camel_case_setters()
    {
        $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

        $dto->setFirstName('Jane');
        $this->assertEquals('Jane', $dto->first_name);
        $this->assertEquals('Jane', $dto->getFirstName());
    }

    /** @test */
    public function it_can_access_snake_case_properties_via_camel_case_magic_get()
    {
        $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

        $this->assertEquals('John', $dto->firstName);
        $this->assertEquals('Doe', $dto->lastName);
    }

    /** @test */
    public function it_can_set_snake_case_properties_via_camel_case_magic_set()
    {
        $dto = new SnakeDto(['first_name' => 'John', 'last_name' => 'Doe']);

        $dto->firstName = 'Jane';
        $this->assertEquals('Jane', $dto->first_name);
        $this->assertEquals('Jane', $dto->firstName);
    }

    /** @test */
    public function it_can_check_snake_case_properties_via_camel_case_magic_isset()
    {
        $dto = new SnakeDto(['first_name' => 'John']);

        $this->assertTrue(isset($dto->firstName));
        $this->assertFalse(isset($dto->lastName));
    }
}

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
