<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Form\Type;

use Runalyze\Bundle\CoreBundle\Form\Type\DurationNullableType;

class DurationNullableTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var DurationNullableType */
    protected $Type;

    protected function setUp()
    {
        $this->Type = new DurationNullableType();
    }

    public function testReverseTransform()
    {
        $this->assertNull($this->Type->reverseTransform('0:00'));
    }
}
