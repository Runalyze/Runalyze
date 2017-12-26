<?php

namespace Runalyze\Tests\Service\WeatherForecast\Strategy;

use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\Strategy\StrategyCollection;
use Runalyze\Service\WeatherForecast\Strategy\StrategyInterface;

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
        $this->assertNull($this->Collection->tryToLoadForecast(new Location()));
        $this->assertNull($this->Collection->getLastSuccessfulStrategy());
    }

    public function testOneValidStrategy()
    {
        $exampleData = new WeatherData();
        $exampleData->Temperature = 5;

        $strategy = $this->getMockForAbstractClass(StrategyInterface::class);
        $strategy->expects($this->any())
            ->method('isPossible')
            ->willReturn(true);
        $strategy->expects($this->any())
            ->method('loadForecast')
            ->willReturn($exampleData);

        /** @var StrategyInterface $strategy */

        $this->Collection->add($strategy);

        $result = $this->Collection->tryToLoadForecast(new Location());

        $this->assertNotNull($result);
        $this->assertEquals(5, $result->Temperature);
        $this->assertEquals($strategy, $this->Collection->getLastSuccessfulStrategy());
    }
}
