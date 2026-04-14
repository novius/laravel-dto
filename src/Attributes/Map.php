<?php

namespace Novius\LaravelDto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Map
{
    public function __construct(public string $name) {}
}
