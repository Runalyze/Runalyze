<?php

namespace Runalyze\Tests\Service\ElevationCorrection\Strategy;

use Runalyze\Service\ElevationCorrection\Strategy\StrategyCollection;
use Runalyze\Service\ElevationCorrection\Strategy\StrategyInterface;

class StrategyCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var StrategyCollection */
    protected $Collection;

    protected function setUp()
    {
        $this->Collection = new StrategyCollection();
    }

    public function testEmptyCollection()
    {
        $this->assertNull($this->Collection->getLastSuccessfulStrategy());
        $this->assertNull($this->Collection->loadAltitudeData([], []));
        $this->assertNull($this->Collection->getLastSuccessfulStrategy());
    }

    public function testNonMatchingArraySizes()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->Collection->loadAltitudeData([49.9, 49.8], [7.7]);
    }

    public function testOneValidStrategy()
    {
        $altitudes = [123, 121, 115];
        $strategy = $this->getMockForAbstractClass(StrategyInterface::class);
        $strategy->expects($this->any())
            ->method('isPossible')
            ->willReturn(true);
        $strategy->expects($this->any())
            ->method('loadAltitudeData')
            ->willReturn($altitudes);

        /** @var StrategyInterface $strategy */

        $this->Collection->add($strategy);

        $result = $this->Collection->loadAltitudeData([49.9, 49.8, 49.7], [7.7, 7.6, 7.5]);

        $this->assertEquals($altitudes, $result);
        $this->assertEquals($strategy, $this->Collection->getLastSuccessfulStrategy());
    }
}
