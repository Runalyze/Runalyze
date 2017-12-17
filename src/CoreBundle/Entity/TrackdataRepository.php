<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TrackdataRepository extends EntityRepository
{
    /**
     * @param int $activityId
     * @param Account $account
     * @return null|Trackdata
     */
    public function findByActivity($activityId, Account $account)
    {
        return $this->findOneBy([
            'activity' => $activityId,
            'account' => $account
        ]);
    }

    public function save(Trackdata $trackdata)
    {
        $this->_em->persist($trackdata);
        $this->_em->flush();
    }
}
