<?php

namespace Novius\LaravelDto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast
{
    public function __construct(public mixed $type) {}
}
