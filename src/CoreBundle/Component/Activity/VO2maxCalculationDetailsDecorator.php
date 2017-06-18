<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Calculation\Elevation\DistanceModifier;
use Runalyze\Calculation\JD\LegacyEffectiveVO2max;

class VO2maxCalculationDetailsDecorator
{
    /** @var ActivityContext */
    protected $Context;

    /** @var RunalyzeConfigurationList */
    protected $ConfigurationList;

    public function __construct(ActivityContext $context, RunalyzeConfigurationList $configurationList)
    {
        $this->Context = $context;
        $this->ConfigurationList = $configurationList;
    }

    /**
     * @return int [%]
     */
    public function getPercentVO2maxVelocityByHeartRate()
    {
        return (int)round(100.0 * LegacyEffectiveVO2max::percentageAt(
            $this->Context->getActivity()->getPulseAvg() / $this->ConfigurationList->getMaximalHeartRate()
        ));
    }

    /**
     * @return float
     */
    public function getCorrectedVO2max()
    {
        return $this->Context->getActivity()->getVO2max() * $this->ConfigurationList->getVO2maxCorrectionFactor();
    }

    /**
     * @return float
     */
    public function getCorrectedVO2maxWithElevationAdjustment()
    {
        return $this->Context->getActivity()->getVO2maxWithElevation() * $this->ConfigurationList->getVO2maxCorrectionFactor();
    }

    /**
     * @return DistanceModifier
     */
    public function getDistanceModifierForElevationAdjustment()
    {
        $modifier = new DistanceModifier(
            $this->Context->getActivity()->getDistance(),
            $this->Context->getDecorator()->getElevationUp(),
            $this->Context->getDecorator()->getElevationDown()
        );
        $modifier->setCorrectionValues(
            $this->ConfigurationList->getVO2max()->getAdditionalDistancePerPositiveElevation(),
            $this->ConfigurationList->getVO2max()->getAdditionalDistancePerNegativeElevation()
        );

        return $modifier;
    }
}
