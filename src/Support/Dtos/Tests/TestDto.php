<?php

namespace Lucent\Support\Dtos\Tests;

use Lucent\Support\Dtos\Dto;
use Lucent\Support\Dtos\Workshop\IgnoresUnknownAttributes;

/**
 * @property string $attr1
 * @property string $attr2
 */
class TestDto extends Dto implements IgnoresUnknownAttributes
{


}
