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

    /**
     * @param $pipedData
     * @param $rawData
     * @dataProvider provideData
     */
    public function testSimpleData($pipedData, $rawData)
    {

        $this->assertSame($pipedData, $this->Type->convertToDatabaseValue($rawData, $this->PlatformMock));
        $this->assertSame($rawData, $this->Type->convertToPHPValue($pipedData, $this->PlatformMock));
    }

    public function provideData()
    {
        return [
            [
                '3.14|42|0',
                [3.14, 42, 0]
            ],
            [
                'foo|bar',
                ['foo', 'bar']
            ],
            [
                'coffee|abba', // hexadecimal characters only
                ['coffee', 'abba']
            ],
            [
                '68e7|1234', // first is a geohash and must not be converted
                ['68e7', 1234]
            ]
        ];
    }
}
