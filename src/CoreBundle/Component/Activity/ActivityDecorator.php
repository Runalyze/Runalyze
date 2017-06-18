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
}
