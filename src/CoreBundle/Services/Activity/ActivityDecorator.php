<?php

namespace Runalyze\Bundle\CoreBundle\Services\Activity;

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
        if ('' != $this->Context->getActivity()->getComment()) {
            return sprintf('%s: %s', $this->Context->getSport()->getName(), $this->Context->getActivity()->getComment());
        }

        if (null !== $this->Context->getActivity()->getType()) {
            return $this->Context->getActivity()->getType()->getName();
        }

        return $this->Context->getSport()->getName();
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
        if ($configurationList->useVO2maxCorrectionForElevation() && $this->Context->getActivity()->getVdotWithElevation() > 0.0) {
            return $this->Context->getActivity()->getVdotWithElevation();
        }

        return $this->Context->getActivity()->getVdot();
    }
}
