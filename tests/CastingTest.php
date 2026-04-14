<?php

namespace Novius\LaravelDto\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use Novius\LaravelDto\Dto;

beforeEach(function () {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

test('it casts to date', function () {
    $dto = new CastDto(['date' => '2024-01-01']);
    expect($dto->date)->toBeInstanceOf(Carbon::class)
        ->and($dto->date->format('Y-m-d'))->toBe('2024-01-01');
});

test('it casts to datetime', function () {
    $dto = new CastDto(['datetime' => '2024-01-01 12:00:00']);
    expect($dto->datetime)->toBeInstanceOf(Carbon::class)
        ->and($dto->datetime->format('Y-m-d H:i:s'))->toBe('2024-01-01 12:00:00');
});

test('it casts to immutable date', function () {
    $dto = new CastDto(['immutable_date' => '2024-01-01']);
    expect($dto->immutable_date)->toBeInstanceOf(CarbonImmutable::class);
});

test('it casts to immutable datetime', function () {
    $dto = new CastDto(['immutable_datetime' => '2024-01-01 12:00:00']);
    expect($dto->immutable_datetime)->toBeInstanceOf(CarbonImmutable::class);
});

test('it casts to decimal', function () {
    $dto = new CastDto(['decimal' => '12.3456']);
    expect($dto->decimal)->toBe('12.35');
});

test('it casts to json', function () {
    $dto = new CastDto(['json' => '{"foo":"bar"}']);
    expect($dto->json)->toBeArray()
        ->and($dto->json)->toBe(['foo' => 'bar']);
});

test('it casts to encrypted', function () {
    $value = 'secret';
    $encrypted = Crypt::encryptString($value);
    $dto = new CastDto(['encrypted' => $encrypted]);
    expect($dto->encrypted)->toBe($value);
});

test('it casts to boolean with filter var', function () {
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

    expect($dto->bool_true)->toBeTrue()
        ->and($dto->bool_false)->toBeFalse()
        ->and($dto->bool_yes)->toBeTrue()
        ->and($dto->bool_no)->toBeFalse()
        ->and($dto->bool_on)->toBeTrue()
        ->and($dto->bool_off)->toBeFalse()
        ->and($dto->bool_1)->toBeTrue()
        ->and($dto->bool_0)->toBeFalse()
        ->and($dto->bool_native_true)->toBeTrue()
        ->and($dto->bool_native_false)->toBeFalse();
});

test('it can convert to array with casts', function () {
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

    expect($array['date'])->toBe('2024-01-01')
        ->and($array['datetime'])->toBe('2024-01-01 12:00:00')
        ->and($array['immutable_date'])->toBe('2024-01-01')
        ->and($array['immutable_datetime'])->toBe('2024-01-01 12:00:00')
        ->and($array['decimal'])->toBe('12.35')
        ->and($array['json'])->toBe(['foo' => 'bar'])
        ->and($array['encrypted'])->toBe('secret')
        ->and($array['bool_true'])->toBeTrue()
        ->and($array['bool_false'])->toBeFalse();
});

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
