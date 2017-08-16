<?php

namespace Runalyze\Bundle\CoreBundle\Services\Activity;

use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Entity\Training;

class ActivityContextFactory
{
    /** @var EntityManager */
    protected $EntityManager;

    public function __construct(EntityManager $em)
    {
        $this->EntityManager = $em;
    }

    /**
     * @param Training $activity
     * @return ActivityContext
     */
    public function getContext(Training $activity)
    {
        $activityId = $activity->getId();

        return new ActivityContext(
            $activity,
            $this->EntityManager->getRepository('CoreBundle:Trackdata')->findByActivity($activityId),
            $this->EntityManager->getRepository('CoreBundle:Swimdata')->findByActivity($activityId),
            $activity->getRoute(),
            $this->EntityManager->getRepository('CoreBundle:Hrv')->findByActivity($activityId),
            $this->EntityManager->getRepository('CoreBundle:Raceresult')->findByActivity($activityId)
        );
    }

    /**
     * @param int $activityId
     * @param int $accountId
     * @return ActivityContext
     *
     * @throws \InvalidArgumentException
     */
    public function getContextById($activityId, $accountId)
    {
        $activity = $this->EntityManager->getRepository('CoreBundle:Training')->findForAccount($activityId, $accountId);

        if (null === $activity) {
            throw new \InvalidArgumentException('Unknown activity (id = '.$activityId.').');
        }

        return $this->getContext($activity);
    }
}
