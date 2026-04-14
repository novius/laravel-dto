<?php

namespace Novius\LaravelDto\Tests;

use Carbon\Carbon;
use Novius\LaravelDto\Dto;

test('it can cast date with custom format in to array', function () {
    $dto = new FormattedDateDto(['date' => '2024-04-14']);

    $array = $dto->toArray();

    expect($array['date'])->toBe('14/04/2024');
});

test('it can cast datetime with custom format in to array', function () {
    $dto = new FormattedDateDto(['datetime' => '2024-04-14 10:45:00']);

    $array = $dto->toArray();

    expect($array['datetime'])->toBe('2024-04-14 10:45');
});

/**
 * @property Carbon $date
 * @property Carbon $datetime
 */
class FormattedDateDto extends Dto
{
    protected Carbon $date;

    protected Carbon $datetime;

    protected function casts(): array
    {
        return [
            'date' => 'date:d/m/Y',
            'datetime' => 'datetime:Y-m-d H:i',
        ];
    }
}
