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

    /**
     * @param Account $account
     * @param int $sportid
     * @param int $year
     * @return array
     */
    public function findBySportAndYear(Account $account, $sportid, $year)
    {
        return $this->_em->createQueryBuilder()
            ->select(
                'r',
                't.time'
            )
            ->from('CoreBundle:Raceresult', 'r')
            ->join('r.activity', 't')
            ->where('r.account = :account')
            ->andWhere('t.sport = :sport')
            ->andWhere('t.time BETWEEN :startTime and :endTime')
            ->setParameters([
                ':account' => $account->getId(),
                ':sport' => $sportid,
                ':startTime' => mktime(0, 0, 0, 1, 1, $year),
                ':endTime' => mktime(23, 59, 59, 12, 31, $year)
            ])
            ->getQuery()
            ->getArrayResult();
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
