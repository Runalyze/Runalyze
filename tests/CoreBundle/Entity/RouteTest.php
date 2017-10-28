<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Route */
    protected $Route;

    public function setUp()
    {
        $this->Route = new Route();
    }

    public function testEmptyEntity()
    {
        $this->assertTrue($this->Route->isEmpty());
        $this->assertNull($this->Route->getGeohashes());
        $this->assertNull($this->Route->getElevations());
    }

    public function testThatLatitudesAndLongitudesMustBeOfSameSize()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->Route->setLatitudesAndLongitudes(
            [49.77, 49.78, 49.79],
            [7.69, 7.69]
        );
    }

    public function testLatitudesAndLongitudes()
    {
        $coordinates = [
            [49.778, 49.781, 49.783],
            [7.696, 7.694, 7.695]
        ];

        $this->Route->setLatitudesAndLongitudes($coordinates[0], $coordinates[1]);

        $this->assertEquals($coordinates, $this->Route->getLatitudesAndLongitudes());
        $this->assertEquals($coordinates[0], $this->Route->getLatitudes());
        $this->assertEquals($coordinates[1], $this->Route->getLongitudes());
    }

    public function testFlagForSynchronizedMinMax()
    {
        $this->assertTrue($this->Route->areMinMaxGeohashSynchronized());

        $this->Route->setGeohashes(['7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhj49qr']);

        $this->assertFalse($this->Route->areMinMaxGeohashSynchronized());
    }

    public function testFlagForSynchronizedMinMaxWhenSettingLatitudesAndLongitudes()
    {
        $this->assertTrue($this->Route->areMinMaxGeohashSynchronized());

        $this->Route->setLatitudesAndLongitudes(
            [49.77, 49.78],
            [7.69, 7.69]
        );

        $this->assertFalse($this->Route->areMinMaxGeohashSynchronized());
    }
}
