<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;

class ActivityDecorator
{
    /** @var ActivityContext */
    protected $Context;

    public function __construct(ActivityContext $context)
    {
        $this->Context = $context;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ('' != $this->Context->getActivity()->getTitle()) {
            return sprintf('%s: %s', $this->Context->getSport()->getName(), $this->Context->getActivity()->getTitle());
        }

        if (null !== $this->Context->getActivity()->getType()) {
            return $this->Context->getActivity()->getType()->getName();
        }

        return $this->Context->getSport()->getName();
    }

    /**
     * @return int|null [m]
     */
    public function getElevationUp()
    {
        return $this->getElevation();
    }

    /**
     * @return int|null [m]
     */
    public function getElevationDown()
    {
        return $this->getElevation(false);
    }

    /**
     * @param bool $up
     * @return int|null
     */
    protected function getElevation($up = true)
    {
        if ($this->Context->hasRoute() && ($this->Context->getRoute()->getElevationUp() > 0 || $this->Context->getRoute()->getElevationDown() > 0)) {
            return $up ? $this->Context->getRoute()->getElevationUp() : $this->Context->getRoute()->getElevationDown();
        }

        return $this->Context->getActivity()->getElevation();
    }

    /**
     * @param RunalyzeConfigurationList $configurationList
     * @return float
     */
    public function getEffectiveVO2max(RunalyzeConfigurationList $configurationList)
    {
        return $configurationList->getVO2maxCorrectionFactor() * $this->getUncorrectedVO2max($configurationList);
    }

    /**
     * @param RunalyzeConfigurationList $configurationList
     * @return float
     */
    public function getUncorrectedVO2max(RunalyzeConfigurationList $configurationList)
    {
        if ($configurationList->useVO2maxCorrectionForElevation() && $this->Context->getActivity()->getVO2maxWithElevation() > 0.0) {
            return $this->Context->getActivity()->getVO2maxWithElevation();
        }

        return $this->Context->getActivity()->getVO2max();
    }

    /**
     * @return float|null [ms] can be negative for walking
     */
    public function getFlightTime()
    {
        $cadence = $this->Context->getActivity()->getCadence();
        $groundContactTime = $this->Context->getActivity()->getGroundcontact();

        if ($cadence > 0 && $groundContactTime > 0) {
            return 30000.0 / $cadence - $groundContactTime;
        }

        return null;
    }

    /**
     * @return float|null [%] can be negative for walking
     */
    public function getFlightRatio()
    {
        $cadence = $this->Context->getActivity()->getCadence();
        $groundContactTime = $this->Context->getActivity()->getGroundcontact();

        if ($cadence > 0 && $groundContactTime > 0) {
            return 1.0 - $cadence * $groundContactTime / 30000.0;
        }

        return null;
    }
}
