<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity;

use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Training;

class ActivityPreview
{
    /** @var int */
    protected $ActivityId;

    /** @var string */
    protected $Title;

    /** @var \DateTime */
    protected $DateTime;

    /** @var int|float [s] */
    protected $Duration;

    /** @var float|null [km] */
    protected $Distance = null;

    /** @var string */
    protected $SportName = '';

    /** @var string */
    protected $SportIcon = '';

    /** @var bool */
    protected $HasHeartRate;

    /** @var bool */
    protected $HasRoute;

    /** @var bool */
    protected $HasRounds;

    /** @var bool */
    protected $IsPossibleDuplicate;

    public function __construct(Training $activity, $isPossibleDuplicate = false)
    {
        $this->setSimplePropertiesFromActivity($activity);
        $this->setPropertiesFromSport($activity->getSport());
        $this->setPropertiesFromDecorator(new ActivityDecorator(
            new ActivityContext($activity)
        ));

        $this->IsPossibleDuplicate = $isPossibleDuplicate;
    }

    protected function setSimplePropertiesFromActivity(Training $activity)
    {
        $this->ActivityId = $activity->getId();
        $this->Duration = $activity->getS();
        $this->Distance = $activity->getDistance();
        $this->HasHeartRate = null !== $activity->getPulseAvg();
        $this->HasRoute = $activity->hasRoute();
        $this->HasRounds = !$activity->getSplits()->isEmpty();
    }

    protected function setPropertiesFromSport(Sport $sport = null)
    {
        if (null === $sport) {
            return;
        }

        $this->SportName = $sport->getName();
        $this->SportIcon = $sport->getImg();
    }

    protected function setPropertiesFromDecorator(ActivityDecorator $decorator)
    {
        $this->Title = $decorator->getTitle(true);
        $this->DateTime = $decorator->getDateTime();
    }

    /**
     * @param bool $flag
     */
    public function setPossibleDuplicate($flag)
    {
        $this->IsPossibleDuplicate = $flag;
    }

    /**
     * @return int
     */
    public function getActivityId()
    {
        return $this->ActivityId;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->Title;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->DateTime;
    }

    /**
     * @return int|float [s]
     */
    public function getDuration()
    {
        return $this->Duration;
    }

    /**
     * @return float|null [km]
     */
    public function getDistance()
    {
        return $this->Distance;
    }

    /**
     * @return bool
     */
    public function hasDistance()
    {
        return null !== $this->Distance;
    }

    /**
     * @return string
     */
    public function getSportName()
    {
        return $this->SportName;
    }

    /**
     * @return string
     */
    public function getSportIcon()
    {
        return $this->SportIcon;
    }

    /**
     * @return bool
     */
    public function hasHeartRate()
    {
        return $this->HasHeartRate;
    }

    /**
     * @return bool
     */
    public function hasRoute()
    {
        return $this->HasRoute;
    }

    /**
     * @return bool
     */
    public function hasRounds()
    {
        return $this->HasRounds;
    }

    /**
     * @return bool
     */
    public function isPossibleDuplicate()
    {
        return $this->IsPossibleDuplicate;
    }
}
