<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TrackdataRepository extends EntityRepository
{
    /**
     * @param int $activityId
     * @return null|Trackdata
     */
    public function findByActivity($activityId)
    {
        return $this->findOneBy([
            'activity' => $activityId
        ]);
    }

    public function save(Trackdata $trackdata)
    {
        $this->_em->persist($trackdata);
        $this->_em->flush();
    }
}
