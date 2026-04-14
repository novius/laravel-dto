<?php

namespace Novius\LaravelDto\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use Novius\LaravelDto\Dto;
use Orchestra\Testbench\TestCase;

class CastingTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    /** @test */
    public function it_casts_to_date()
    {
        $dto = new CastDto(['date' => '2024-01-01']);
        $this->assertInstanceOf(Carbon::class, $dto->date);
        $this->assertEquals('2024-01-01', $dto->date->format('Y-m-d'));
    }

    /** @test */
    public function it_casts_to_datetime()
    {
        $dto = new CastDto(['datetime' => '2024-01-01 12:00:00']);
        $this->assertInstanceOf(Carbon::class, $dto->datetime);
        $this->assertEquals('2024-01-01 12:00:00', $dto->datetime->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_casts_to_immutable_date()
    {
        $dto = new CastDto(['immutable_date' => '2024-01-01']);
        $this->assertInstanceOf(CarbonImmutable::class, $dto->immutable_date);
    }

    /** @test */
    public function it_casts_to_immutable_datetime()
    {
        $dto = new CastDto(['immutable_datetime' => '2024-01-01 12:00:00']);
        $this->assertInstanceOf(CarbonImmutable::class, $dto->immutable_datetime);
    }

    /** @test */
    public function it_casts_to_decimal()
    {
        $dto = new CastDto(['decimal' => '12.3456']);
        $this->assertEquals('12.35', $dto->decimal);
    }

    /** @test */
    public function it_casts_to_json()
    {
        $dto = new CastDto(['json' => '{"foo":"bar"}']);
        $this->assertIsArray($dto->json);
        $this->assertEquals(['foo' => 'bar'], $dto->json);
    }

    /** @test */
    public function it_casts_to_encrypted()
    {
        $value = 'secret';
        $encrypted = Crypt::encryptString($value);
        $dto = new CastDto(['encrypted' => $encrypted]);
        $this->assertEquals($value, $dto->encrypted);
    }

    /** @test */
    public function it_casts_to_boolean_with_filter_var()
    {
        $dto = new CastDto([
            'bool_true' => 'true',
            'bool_false' => 'false',
            'bool_yes' => 'yes',
            'bool_no' => 'no',
            'bool_on' => 'on',
            'bool_off' => 'off',
            'bool_1' => '1',
            'bool_0' => '0',
            'bool_native_true' => true,
            'bool_native_false' => false,
        ]);

        $this->assertTrue($dto->bool_true);
        $this->assertFalse($dto->bool_false);
        $this->assertTrue($dto->bool_yes);
        $this->assertFalse($dto->bool_no);
        $this->assertTrue($dto->bool_on);
        $this->assertFalse($dto->bool_off);
        $this->assertTrue($dto->bool_1);
        $this->assertFalse($dto->bool_0);
        $this->assertTrue($dto->bool_native_true);
        $this->assertFalse($dto->bool_native_false);
    }

    /** @test */
    public function it_can_convert_to_array_with_casts()
    {
        $dto = new CastDto([
            'date' => '2024-01-01',
            'datetime' => '2024-01-01 12:00:00',
            'immutable_date' => '2024-01-01',
            'immutable_datetime' => '2024-01-01 12:00:00',
            'decimal' => '12.3456',
            'json' => '{"foo":"bar"}',
            'encrypted' => Crypt::encryptString('secret'),
            'bool_true' => 'true',
            'bool_false' => 'false',
            'bool_yes' => 'yes',
            'bool_no' => 'no',
            'bool_on' => 'on',
            'bool_off' => 'off',
            'bool_1' => '1',
            'bool_0' => '0',
            'bool_native_true' => true,
            'bool_native_false' => false,
        ]);

        $array = $dto->toArray();

        $this->assertEquals('2024-01-01', $array['date']);
        $this->assertEquals('2024-01-01 12:00:00', $array['datetime']);
        $this->assertEquals('2024-01-01', $array['immutable_date']);
        $this->assertEquals('2024-01-01 12:00:00', $array['immutable_datetime']);
        $this->assertEquals('12.35', $array['decimal']);
        $this->assertEquals(['foo' => 'bar'], $array['json']);
        $this->assertEquals('secret', $array['encrypted']);
        $this->assertTrue($array['bool_true']);
        $this->assertFalse($array['bool_false']);
    }
}

/**
 * @property Carbon $date
 * @property Carbon $datetime
 * @property CarbonImmutable $immutable_date
 * @property CarbonImmutable $immutable_datetime
 * @property string $decimal
 * @property array $json
 * @property string $encrypted
 * @property bool $bool_true
 * @property bool $bool_false
 * @property bool $bool_yes
 * @property bool $bool_no
 * @property bool $bool_on
 * @property bool $bool_off
 * @property bool $bool_1
 * @property bool $bool_0
 * @property bool $bool_native_true
 * @property bool $bool_native_false
 */
class CastDto extends Dto
{
    protected mixed $date;

    protected mixed $datetime;

    protected mixed $immutable_date;

    protected mixed $immutable_datetime;

    protected mixed $decimal;

    protected mixed $json;

    protected mixed $encrypted;

    protected mixed $bool_true;

    protected mixed $bool_false;

    protected mixed $bool_yes;

    protected mixed $bool_no;

    protected mixed $bool_on;

    protected mixed $bool_off;

    protected mixed $bool_1;

    protected mixed $bool_0;

    protected mixed $bool_native_true;

    protected mixed $bool_native_false;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'datetime' => 'datetime',
            'immutable_date' => 'immutable_date',
            'immutable_datetime' => 'immutable_datetime',
            'decimal' => 'decimal:2',
            'json' => 'json',
            'encrypted' => 'encrypted',
            'bool_true' => 'bool',
            'bool_false' => 'bool',
            'bool_yes' => 'bool',
            'bool_no' => 'bool',
            'bool_on' => 'bool',
            'bool_off' => 'bool',
            'bool_1' => 'bool',
            'bool_0' => 'bool',
            'bool_native_true' => 'bool',
            'bool_native_false' => 'bool',
        ];
    }
}
