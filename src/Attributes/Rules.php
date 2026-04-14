<?php

namespace Novius\LaravelDto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rules
{
    public function __construct(public string|array $rules) {}
}
