<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Form\Type;

use Runalyze\Bundle\CoreBundle\Form\Type\AbstractUnitBasedType;
use Runalyze\Metrics\Common\Unit\None;

class AbstractUnitBasedTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractUnitBasedType */
    protected $Type;

    protected function setUp()
    {
        $this->Type = $this->getMockForAbstractClass(AbstractUnitBasedType::class, [new None()]);
    }

    public function testTransform()
    {
        $this->assertEquals('', $this->Type->transform(null));
        $this->assertEquals('1.2', $this->Type->transform(1.234));
        $this->assertEquals('7.7', $this->Type->transform('7.69'));
    }

    public function testReverseTransform()
    {
        $this->assertEquals(null, $this->Type->reverseTransform(null));
        $this->assertEquals(1.23, $this->Type->reverseTransform('1.23'));
        $this->assertEquals(7.69, $this->Type->reverseTransform('7,69'));
    }
}
