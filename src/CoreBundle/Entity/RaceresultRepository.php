<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RaceresultRepository extends EntityRepository
{
    /**
     * @param int $activityId
     * @return null|Raceresult
     */
    public function findByActivity($activityId)
    {
        return $this->findOneBy([
            'activity' => $activityId
        ]);
    }

    public function save(Raceresult $raceResult)
    {
        $this->_em->persist($raceResult);
        $this->_em->flush();
    }
}
