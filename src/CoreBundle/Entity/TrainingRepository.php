<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Model\Account\AccountStatistics;

class TrainingRepository extends EntityRepository
{
    /**
     * @param bool $cache
     * @return float
     */
    public function getAmountOfLoggedKilometers($cache = true)
    {
		return $this->createQueryBuilder('t')
			->select('SUM(t.distance)')
			->getQuery()
			->useResultCache($cache)
			->setResultCacheLifetime(120)
			->getSingleScalarResult();
    }

    /**
     * @param int $activityid
     * @param int $accountid
     * @return mixed
     */
	public function getSpeedUnitFor($activityid, $accountid)
    {
        return $this->_em->createQueryBuilder()
            ->select('s.speed')
            ->from('CoreBundle:Training', 't')
            ->join('t.sport', 's')
            ->where('t.id = :id AND t.account = :account')
            ->setParameter('id', $activityid)
            ->setParameter('account', $accountid)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Account $account
     * @param null|string $column
     * @param null|int $sportid
     * @return array
     */
    public function getMonthlyStatsFor(Account $account, $column = null, $sportid = null)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select(
                'YEAR(FROM_UNIXTIME(t.time)) AS year',
                'MONTH(FROM_UNIXTIME(t.time)) AS month'
            )
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->setParameter('account', $account->getId())
            ->addGroupBy('year')
            ->addGroupBy('month');

        if (null !== $column) {
            $queryBuilder->addSelect('SUM(t.'.$column.') AS value');
        } else {
            $queryBuilder->addSelect('SUM(1g) AS value');
        }

        if (null !== $sportid) {
            $queryBuilder
                ->andWhere('t.sport = :sportid')
                ->setParameter('sportid', $sportid);
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }

	/**
	 * @param int $activityId
	 * @param int $accountId
	 * @return null|Training
	 */
	public function findForAccount($activityId, $accountId)
	{
		return $this->findOneBy([
			'id' => $activityId,
			'account' => $accountId
		]);
	}

    /**
     * @param Account $account
     * @return AccountStatistics
     */
    public function getAccountStatistics(Account $account)
    {
        $statistics = new AccountStatistics();

        $dataForAccount = $this->_em->createQueryBuilder()
            ->select(
                'COUNT(1) as num',
                'SUM(t.distance) as distance',
                'SUM(t.s) as duration'
            )
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->setParameter('account', $account->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $dataForAccount) {
            $statistics->setNumberOfActivities($dataForAccount['num']);
            $statistics->setTotalDistance($dataForAccount['distance']);
            $statistics->setTotalDuration($dataForAccount['duration']);
        }

        return $statistics;
    }

    public function getQueryForJsonPosterData(Account $account, $sportid, $year)
    {
        return $this->_em->createQueryBuilder()
            ->select(
                't.s',
                't.time',
                't.distance',
                'r.geohashes'
            )
            ->from('CoreBundle:Training', 't')
            ->join('t.route', 'r')
            ->where('t.account = :account')
            ->andWhere('t.sport = :sport')
            ->andWhere('t.distance > 0')
            ->andWhere('t.time BETWEEN :startTime and :endTime')
            ->setParameters([
                ':account' => $account->getId(),
                ':sport' => $sportid,
                ':startTime' => mktime(0, 0, 0, 1, 1, $year),
                ':endTime' => mktime(23, 59, 59, 12, 31, $year),
                ':empty' => ''
            ])
            ->getQuery();
    }

    /**
     * @param Account $account
     * @return Training[]
     */
    public function accountHasLockedTrainings(Account $account)
    {
        return $this->createQueryBuilder('t')
            ->select('t.id')
            ->setMaxResults(1)
            ->where('t.account = :accountid AND t.lock = 1')
            ->setParameter('accountid', $account->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Training $training)
    {
        $this->_em->persist($training);
        $this->_em->flush();
    }
}
