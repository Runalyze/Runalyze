<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RaceresultRepository extends EntityRepository
{
    /** @var int */
    const NUMBER_OF_RACES_TO_CONSIDER_FOR_CORRECTION_FACTOR = 3;

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
     * @return null|Raceresult
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
     * @param Sport $sport
     * @param int $year
     * @return array
     */
    public function findBySportAndYear(Account $account, Sport $sport, $year)
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
                ':sport' => $sport->getId(),
                ':startTime' => mktime(0, 0, 0, 1, 1, $year),
                ':endTime' => mktime(23, 59, 59, 12, 31, $year)
            ])
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param Account $account
     * @return array|Raceresult[]
     */
    public function findAllWithActivityStats(Account $account)
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->addSelect('partial a.{id, time, sport, vo2max, vo2maxByTime, vo2maxWithElevation}')
            ->join('r.activity', 'a')
            ->where('r.account = :account')
            ->setParameters([
                ':account' => $account
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Account $account
     * @param int $sportId
     * @return float
     */
    public function getEffectiveVO2maxCorrectionFactor(Account $account, $sportId)
    {
        $result = $this->createQueryBuilder('r')
            ->join('r.activity', 't')
            ->select([
                't.vo2maxByTime * 1.0 / t.vo2max as factor'
            ])
            ->where('r.account = :account')
            ->andWhere('t.sport = :sport')
            ->andWhere('t.useVO2max = 1')
            ->andWhere('t.vo2max > 0')
            ->setParameter('account', $account->getId())
            ->setParameter('sport', $sportId)
            ->orderBy('t.vo2maxByTime', 'DESC')
            ->setMaxResults(self::NUMBER_OF_RACES_TO_CONSIDER_FOR_CORRECTION_FACTOR)
            ->getQuery()
            ->getResult("COLUMN_HYDRATOR");

        if (empty($result)) {
            return 1.0;
        }

        return max(array_map(function($v) {
            return (float)$v;
        }, $result));
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
