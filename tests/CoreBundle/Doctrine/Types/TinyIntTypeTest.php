<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Runalyze\Bundle\CoreBundle\Doctrine\Types\TinyIntType;

class TinyIntTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var TinyIntType */
    protected $Type;

    /** @var AbstractPlatform */
    protected $PlatformMock;

    public function setUp()
    {
        $this->Type = TinyIntType::getType(TinyIntType::TINYINT);
        $this->PlatformMock = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    public function testNull()
    {
        $this->assertNull($this->Type->convertToPHPValue(null, $this->PlatformMock));
    }

    public function testSimpleData()
    {
        $this->assertEquals(17, $this->Type->convertToPHPValue('17', $this->PlatformMock));
    }
}
