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

    /**
     * @param int $activityId
     * @param int $accountId
     * @return null|Training
     */
    public function findForAccount($activityId, $accountId)
    {
        return $this->findOneBy([
            'activity' => $activityId,
            'account' => $accountId
        ]);
    }

    public function save(Raceresult $raceResult)
    {
        $this->_em->persist($raceResult);
        $this->_em->flush();
    }

    public function delete(Raceresult $raceResult)
    {
        $this->_em->remove($raceResult);
        $this->_em->flush();
    }
}
