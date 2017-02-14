<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Twig;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Twig\ValueExtension;
use Runalyze\Metrics;

class ValueExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ValueExtension */
    protected $Extension;

    public function setUp()
    {
        $this->Extension = $this->getExtensionFor();
    }

    /**
     * @param array $configurationListData
     * @return ValueExtension
     */
    protected function getExtensionFor(array $configurationListData = [])
    {
        $configManager = $this->getMockBuilder(ConfigurationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configManager
            ->method('getList')->willReturn(new RunalyzeConfigurationList($configurationListData));

        /** @var ConfigurationManager $configManager */
        return new ValueExtension($configManager);
    }

    public function testSimpleValue()
    {
        $value = $this->Extension->value(3.14159, 'pi', 2, ',');

        $this->assertEquals('3,14', $value->getValue());
        $this->assertEquals('pi', $value->getUnit());
    }

    public function testDistanceWithGivenUnit()
    {
        $this->assertEquals('3.14&nbsp;km', $this->Extension->distance(3.14, new Metrics\Distance\Unit\Kilometer())->getWithUnit());
        $this->assertEquals('1.00&nbsp;mi', $this->Extension->distance(1.609, new Metrics\Distance\Unit\Miles())->getWithUnit());
    }

    public function testElevationWithGivenUnit()
    {
        $this->assertEquals('247&nbsp;m', $this->Extension->elevation(247, new Metrics\Distance\Unit\Meter())->getWithUnit());
    }

    public function testStrideLengthWithGivenUnit()
    {
        $this->assertEquals('1.23&nbsp;m', $this->Extension->strideLength(123, new Metrics\Distance\Unit\Meter())->getWithUnit());
        $this->assertEquals('157&nbsp;cm', $this->Extension->strideLength(157, new Metrics\Distance\Unit\Centimeter(), 0)->getWithUnit());
    }

    public function testEnergyWithGivenUnit()
    {
        $this->assertEquals('512&nbsp;kcal', $this->Extension->energy(512, new Metrics\Energy\Unit\Kilocalories())->getWithUnit());
    }

    public function testHeartRateWithGivenUnit()
    {
        $this->assertEquals('120&nbsp;bpm', $this->Extension->heartRate(120, new Metrics\HeartRate\Unit\BeatsPerMinute())->getWithUnit());
        $this->assertEquals('84&nbsp;%', $this->Extension->heartRate(168, new Metrics\HeartRate\Unit\PercentMaximum(200))->getWithUnit());
        $this->assertEquals('67&nbsp;%', $this->Extension->heartRate(140, new Metrics\HeartRate\Unit\PercentReserve(180, 60))->getWithUnit());
    }

    public function testHeartRateComparisonWithGivenUnit()
    {
        $this->assertEquals('10&nbsp;bpm', $this->Extension->heartRateComparison(130, 120, new Metrics\HeartRate\Unit\BeatsPerMinute())->getWithUnit());
        $this->assertEquals('-7&nbsp;bpm', $this->Extension->heartRateComparison(113, 120, new Metrics\HeartRate\Unit\BeatsPerMinute())->getWithUnit());
        $this->assertEquals('2&nbsp;%', $this->Extension->heartRateComparison(172, 168, new Metrics\HeartRate\Unit\PercentMaximum(200))->getWithUnit());
        $this->assertEquals('-15&nbsp;%', $this->Extension->heartRateComparison(122, 140, new Metrics\HeartRate\Unit\PercentReserve(180, 60))->getWithUnit());
    }

    public function testPaceWithGivenUnit()
    {
        $this->assertEquals('5:30/km', $this->Extension->pace(330, new Metrics\Velocity\Unit\SecondsPerKilometer())->getWithUnit());
        $this->assertEquals('10.9&nbsp;km/h', $this->Extension->pace(330, new Metrics\Velocity\Unit\KilometerPerHour())->getWithUnit());
    }

    public function testPaceComparisonWithGivenUnit()
    {
        $this->assertEquals('-0:30/km', $this->Extension->paceComparison(300, 330, new Metrics\Velocity\Unit\SecondsPerKilometer())->getWithUnit());
        $this->assertEquals('0.9&nbsp;km/h', $this->Extension->paceComparison(330, 360, new Metrics\Velocity\Unit\KilometerPerHour())->getWithUnit());
    }

    public function testTemperatureWithGivenUnit()
    {
        $this->assertEquals('13&nbsp;°C', $this->Extension->temperature(13, new Metrics\Temperature\Unit\Celsius())->getWithUnit());
    }

    public function testWeightWithGivenUnit()
    {
        $this->assertEquals('87.3&nbsp;kg', $this->Extension->weight(87.332, new Metrics\Weight\Unit\Kilogram())->getWithUnit());
    }
}
