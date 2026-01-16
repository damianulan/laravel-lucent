<?php

namespace Lucent\Support\Dtos\Tests;

use Lucent\Support\Dtos\LaravelDto;
use DTOs\Workshop\IgnoresUnknownAttributes;

/**
 * @property string $attr1
 * @property string $attr2
 */
class TestDto extends LaravelDto implements IgnoresUnknownAttributes {}
