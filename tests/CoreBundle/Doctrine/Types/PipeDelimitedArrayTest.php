<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Runalyze\Bundle\CoreBundle\Doctrine\Types\PipeDelimitedArray;

class PipeDelimitedArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var PipeDelimitedArray */
    protected $Type;

    /** @var AbstractPlatform */
    protected $PlatformMock;

    public function setUp()
    {
        $this->Type = PipeDelimitedArray::getType(PipeDelimitedArray::PIPE_ARRAY);
        $this->PlatformMock = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    public function testEmptyData()
    {
        $this->assertNull($this->Type->convertToPHPValue(null, $this->PlatformMock));
        $this->assertNull($this->Type->convertToPHPValue('', $this->PlatformMock));
        $this->assertNull($this->Type->convertToDatabaseValue([], $this->PlatformMock));
    }

    public function testSimpleData()
    {
        $this->assertEquals([3.14, 42, 0], $this->Type->convertToPHPValue('3.14|42|0', $this->PlatformMock));
        $this->assertEquals('3.14|42|0', $this->Type->convertToDatabaseValue([3.14, 42, 0], $this->PlatformMock));
    }

    public function testPartiallyEmptyData()
    {
        $this->assertEquals('||-12.3|-11.4|-9.8', $this->Type->convertToDatabaseValue([null, null, -12.3, -11.4, -9.8], $this->PlatformMock));

        $result = $this->Type->convertToPHPValue('||-12.3|-11.4|-9.8', $this->PlatformMock);
        $this->assertEquals([null, null, -12.3, -11.4, -9.8], $result);
        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
    }
}
