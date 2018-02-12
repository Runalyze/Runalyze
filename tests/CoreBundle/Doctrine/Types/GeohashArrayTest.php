<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Runalyze\Bundle\CoreBundle\Doctrine\Types\GeohashArray;

class GeohashArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var GeohashArray */
    protected $Type;

    /** @var AbstractPlatform */
    protected $PlatformMock;

    public function setUp()
    {
        $this->Type = GeohashArray::getType(GeohashArray::GEOHASH_ARRAY);
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
        $longHashes = ['7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhj49qr', 'u1xjnxhjr7wb', '7zzzzzzzzzzz', 'u1xjnxhmqqg2', 'u1xjnxhm6zkm'];
        $shortHashes = implode('|', ['7zzzzzzzzzzz', 'u1xjnxhj49qr', '', 'r7wb', '7zzzzzzzzzzz', 'u1xjnxhmqqg2', '6zkm']);

        $this->assertEquals($longHashes, $this->Type->convertToPHPValue($shortHashes, $this->PlatformMock));
        $this->assertEquals($shortHashes, $this->Type->convertToDatabaseValue($longHashes, $this->PlatformMock));
    }
}
