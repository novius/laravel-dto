<?php

namespace Novius\LaravelDto\Tests;

use Carbon\Carbon;
use Novius\LaravelDto\Dto;
use Orchestra\Testbench\TestCase;

class DateFormatTest extends TestCase
{
    /** @test */
    public function it_can_cast_date_with_custom_format_in_to_array()
    {
        $dto = new FormattedDateDto(['date' => '2024-04-14']);

        $array = $dto->toArray();

        $this->assertEquals('14/04/2024', $array['date']);
    }

    /** @test */
    public function it_can_cast_datetime_with_custom_format_in_to_array()
    {
        $dto = new FormattedDateDto(['datetime' => '2024-04-14 10:45:00']);

        $array = $dto->toArray();

        $this->assertEquals('2024-04-14 10:45', $array['datetime']);
    }
}

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
