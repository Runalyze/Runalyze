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
    public function getVdot(RunalyzeConfigurationList $configurationList)
    {
        return $configurationList->getVdotFactor() * $this->getUncorrectedVdot($configurationList);
    }

    /**
     * @param RunalyzeConfigurationList $configurationList
     * @return float
     */
    public function getUncorrectedVdot(RunalyzeConfigurationList $configurationList)
    {
        if ($configurationList->useVdotCorrectionForElevation() && $this->Context->getActivity()->getVdotWithElevation() > 0.0) {
            return $this->Context->getActivity()->getVdotWithElevation();
        }

        return $this->Context->getActivity()->getVdot();
    }
}
