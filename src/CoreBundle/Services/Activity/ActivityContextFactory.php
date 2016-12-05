<?php

namespace Runalyze\Bundle\CoreBundle\Services\Activity;

use Doctrine\ORM\EntityManager;

class ActivityContextFactory
{
    /** @var EntityManager */
    protected $EntityManager;

    public function __construct(EntityManager $em)
    {
        $this->EntityManager = $em;
    }

    /**
     * @param int $activityId
     * @param int $accountId
     * @return ActivityContext
     */
    public function getContext($activityId, $accountId)
    {
        $activity = $this->EntityManager->getRepository('CoreBundle:Training')->findForAccount($activityId, $accountId);

        if (null === $activity) {
            throw new \InvalidArgumentException('Unknown activity (id = '.$activityId.').');
        }

        return new ActivityContext(
            $activity,
            $this->EntityManager->getRepository('CoreBundle:Trackdata')->findByActivity($activityId),
            $this->EntityManager->getRepository('CoreBundle:Swimdata')->findByActivity($activityId),
            $activity->getRoute(),
            $this->EntityManager->getRepository('CoreBundle:Hrv')->findByActivity($activityId),
            $this->EntityManager->getRepository('CoreBundle:Raceresult')->findByActivity($activityId)
        );
    }
}
