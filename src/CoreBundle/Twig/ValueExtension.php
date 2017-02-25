<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Bundle\CoreBundle\Services\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Metrics\Common\UnitInterface;
use Runalyze\Metrics\Distance\Unit\AbstractDistanceUnit;
use Runalyze\Metrics\Energy\Unit\AbstractEnergyUnit;
use Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnit;
use Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnitInPercent;
use Runalyze\Metrics\Velocity\Unit\AbstractPaceInTimeFormatUnit;
use Runalyze\Metrics\Velocity\Unit\AbstractPaceUnit;
use Runalyze\Metrics\Temperature\Unit\AbstractTemperatureUnit;
use Runalyze\Metrics\Weight\Unit\AbstractWeightUnit;

class ValueExtension extends \Twig_Extension
{
    /** @var UnitSystem */
    protected $UnitSystem;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->UnitSystem = new UnitSystem($configurationManager->getList());
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'runalyze.value_extension';
    }

    /**
     * @return \Twig_SimpleFunction[]
     *
     * @codeCoverageIgnore
     */
    public function getFunctions()
    {
        $safeHtmlOptions = array('is_safe' => ['html']);

        return array(
            new \Twig_SimpleFunction('value', array($this, 'value'), $safeHtmlOptions),
            new \Twig_SimpleFunction('distance', array($this, 'distance'), $safeHtmlOptions),
            new \Twig_SimpleFunction('elevation', array($this, 'elevation'), $safeHtmlOptions),
            new \Twig_SimpleFunction('strideLength', array($this, 'strideLength'), $safeHtmlOptions),
            new \Twig_SimpleFunction('energy', array($this, 'energy'), $safeHtmlOptions),
            new \Twig_SimpleFunction('heartRate', array($this, 'heartRate'), $safeHtmlOptions),
            new \Twig_SimpleFunction('heartRateComparison', array($this, 'heartRateComparison'), $safeHtmlOptions),
            new \Twig_SimpleFunction('pace', array($this, 'pace'), $safeHtmlOptions),
            new \Twig_SimpleFunction('paceComparison', array($this, 'paceComparison'), $safeHtmlOptions),
            new \Twig_SimpleFunction('temperature', array($this, 'temperature'), $safeHtmlOptions),
            new \Twig_SimpleFunction('weight', array($this, 'weight'), $safeHtmlOptions),
            new \Twig_SimpleFunction('vo2max', array($this, 'vo2max'), $safeHtmlOptions),
            new \Twig_SimpleFunction('vo2maxFor', array($this, 'vo2maxFor'), $safeHtmlOptions),
        );
    }

    /**
     * @param mixed $value
     * @param string|UnitInterface $unit
     * @param int $defaultDecimals
     * @param string $defaultDecimalPoint
     * @param string $defaultThousandsSeparator
     * @return DisplayableValue
     */
    public function value($value, $unit, $defaultDecimals = 0, $defaultDecimalPoint = '.', $defaultThousandsSeparator = ',')
    {
        return new DisplayableValue($value, $unit, $defaultDecimals, $defaultDecimalPoint, $defaultThousandsSeparator);
    }

    /**
     * @param float|int $kilometer [km]
     * @param null|AbstractDistanceUnit $unit
     * @param int $decimals
     * @return DisplayableValue
     */
    public function distance($kilometer, $unit = null, $decimals = 2)
    {
        $unit = $unit ?: $this->UnitSystem->getDistanceUnit();

        return new DisplayableValue($kilometer, $unit, $decimals);
    }

    /**
     * @param float|int $meter [m]
     * @param null|AbstractDistanceUnit $unit
     * @return DisplayableValue
     */
    public function elevation($meter, $unit = null)
    {
        $unit = $unit ?: $this->UnitSystem->getElevationUnit();

        return new DisplayableValue($meter / 1000, $unit);
    }

    /**
     * @param float|int $centimeter [cm]
     * @param null|AbstractDistanceUnit $unit
     * @param int $decimals
     * @return DisplayableValue
     */
    public function strideLength($centimeter, $unit = null, $decimals = 2)
    {
        $unit = $unit ?: $this->UnitSystem->getStrideLengthUnit();

        return new DisplayableValue($centimeter / 100000, $unit, $decimals);
    }

    /**
     * @param float|int $kcal [kcal]
     * @param null|AbstractEnergyUnit $unit
     * @return DisplayableValue
     */
    public function energy($kcal, $unit = null)
    {
        $unit = $unit ?: $this->UnitSystem->getEnergyUnit();

        return new DisplayableValue($kcal, $unit);
    }

    /**
     * @param float|int $bpm [bpm]
     * @param null|AbstractHeartRateUnit $unit
     * @return DisplayableValue
     */
    public function heartRate($bpm, $unit = null)
    {
        $unit = $unit ?: $this->UnitSystem->getHeartRateUnit();

        if ($unit instanceof AbstractHeartRateUnitInPercent) {
            return new DisplayableValueInPercent($bpm, $unit);
        }

        return new DisplayableValue($bpm, $unit);
    }

    /**
     * @param float|int $baseValue [bpm]
     * @param float|int $comparisonValue [bpm]
     * @param null|AbstractHeartRateUnit $unit
     * @return DisplayableValue
     */
    public function heartRateComparison($baseValue, $comparisonValue, $unit = null)
    {
        $unit = $unit ?: $this->UnitSystem->getHeartRateUnit();

        return $this->heartRate($unit->compareBaseUnit($baseValue, $comparisonValue), $unit);
    }

    /**
     * @param float|int $secondsPerKilometer [s/km]
     * @param null|AbstractPaceUnit $unit
     * @param int $decimals only for pace units in decimal format
     * @return DisplayableValue
     */
    public function pace($secondsPerKilometer, $unit = null, $decimals = 1)
    {
        $unit = $unit ?: $this->UnitSystem->getPaceUnit();

        if ($unit instanceof AbstractPaceInTimeFormatUnit) {
            return new DisplayablePace($secondsPerKilometer, $unit);
        }

        return new DisplayableValue($secondsPerKilometer, $unit, $decimals);
    }

    /**
     * @param float|int $baseValue [s/km]
     * @param float|int $comparisonValue [s/km]
     * @param null|AbstractPaceUnit $unit
     * @return DisplayableValue
     */
    public function paceComparison($baseValue, $comparisonValue, $unit = null)
    {
        $unit = $unit ?: $this->UnitSystem->getPaceUnit();

        return $this->pace($unit->compareBaseUnit($baseValue, $comparisonValue), $unit);
    }

    /**
     * @param float|int $celsius [°C]
     * @param null|AbstractTemperatureUnit $unit
     * @param int $decimals
     * @return DisplayableValue
     */
    public function temperature($celsius, $unit = null, $decimals = 0)
    {
        $unit = $unit ?: $this->UnitSystem->getTemperatureUnit();

        return new DisplayableValue($celsius, $unit, $decimals);
    }

    /**
     * @param float $kilogram [kg]
     * @param null|AbstractWeightUnit $unit
     * @param int $decimals
     * @return DisplayableValue
     */
    public function weight($kilogram, $unit = null, $decimals = 1)
    {
        $unit = $unit ?: $this->UnitSystem->getWeightUnit();

        return new DisplayableValue($kilogram, $unit, $decimals);
    }

    /**
     * @param float $uncorrectedValue
     * @param RunalyzeConfigurationList $configurationList
     * @param bool $valueIsUsedForShape
     * @return DisplayableVO2max
     */
    public function vo2max($uncorrectedValue, RunalyzeConfigurationList $configurationList, $valueIsUsedForShape = true)
    {
        return new DisplayableVO2max($uncorrectedValue, $configurationList, $valueIsUsedForShape);
    }

    /**
     * @param ActivityContext $activityContext
     * @param RunalyzeConfigurationList $configurationList
     * @return DisplayableVO2max
     */
    public function vo2maxFor(ActivityContext $activityContext, RunalyzeConfigurationList $configurationList)
    {
        return $this->vo2max(
            $activityContext->getDecorator()->getUncorrectedVO2max($configurationList),
            $configurationList,
            $activityContext->getActivity()->getUseVdot()
        );
    }
}
