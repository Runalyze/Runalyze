<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\AbstractQuery;
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
     * @see \Runalyze\Metrics\Velocity\Unit\PaceEnum
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
     * @param null|int $sportid
     * @return array
     */
    public function getActiveYearsFor(Account $account, $sportid = null, $minimumActivities = null)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('YEAR(FROM_UNIXTIME(t.time)) AS year')
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->setParameter('account', $account->getId())
            ->addGroupBy('year');

        if (null !== $sportid) {
            $queryBuilder
                ->andWhere('t.sport = :sportid')
                ->setParameter('sportid', $sportid);
        }

        if (null !== $minimumActivities) {
            $queryBuilder
                ->having('COUNT(IDENTITY(t.sport)) >= :minimum')
                ->setParameter('minimum', $minimumActivities);
        }

        return $queryBuilder->getQuery()->getResult("COLUMN_HYDRATOR");
    }

    /**
     * @param Account $account
     * @param null|int $year
     * @param null|int $sportid
     * @return array
     */
    public function getNumberOfActivitiesFor(Account $account, $year = null, $sportid = null)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('COUNT(1) as num')
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->setParameter('account', $account->getId());

        if (null !== $year) {
            $queryBuilder
                ->andWhere('t.time BETWEEN :startTime and :endTime')
                ->setParameter('startTime', mktime(0, 0, 0, 1, 1, $year))
                ->setParameter('endTime', mktime(23, 59, 59, 12, 31, $year));
        }

        if (null !== $sportid) {
            $queryBuilder
                ->andWhere('t.sport = :sportid')
                ->setParameter('sportid', $sportid);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Type $type
     * @return array
     */
    public function getNumberOfActivitiesWithActivityType(Type $type)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('COUNT(1) as num')
            ->from('CoreBundle:Training', 't')
            ->where('t.type = :typeid')
            ->setParameter('typeid', $type->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Account $account
     * @return array
     */
    public function getTypesWithTraining(Account $account)
    {
        $qb = $this->_em->createQueryBuilder();
        $queryBuilder = $qb
            ->select('IDENTITY(t.type)')
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->andWhere($qb->expr()->isNotNull('t.type'))
            ->addGroupBy('t.type')
            ->setParameter('account', $account->getId());
        return $queryBuilder->getQuery()->getResult("COLUMN_HYDRATOR");
    }

    /**
     * @param Account $account
     * @return array
     */
    public function getSportsWithTraining(Account $account)
    {
        $qb = $this->_em->createQueryBuilder();
        $queryBuilder = $qb
            ->select('IDENTITY(t.sport)')
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->andWhere($qb->expr()->isNotNull('t.sport'))
            ->addGroupBy('t.sport')
            ->setParameter('account', $account->getId());
        return $queryBuilder->getQuery()->getResult("COLUMN_HYDRATOR");
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
            $queryBuilder->addSelect('SUM(1) AS value');
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
            $statistics->setNumberOfActivities((int)$dataForAccount['num']);
            $statistics->setTotalDistance((float)$dataForAccount['distance']);
            $statistics->setTotalDuration((float)$dataForAccount['duration']);
        }

        return $statistics;
    }

    /**
     * @param Account $account
     * @param Sport $sport
     * @param int $year
     * @return \Doctrine\ORM\Query
     */
    public function getQueryForJsonPosterData(Account $account, Sport $sport, $year)
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
                ':sport' => $sport->getId(),
                ':startTime' => mktime(0, 0, 0, 1, 1, $year),
                ':endTime' => mktime(23, 59, 59, 12, 31, $year)
            ])
            ->getQuery();
    }

    /**
     * @param Account $account
     * @param Sport $sport
     * @param int $year
     * @return \Doctrine\ORM\Query
     */
    public function getStatsForPoster(Account $account, Sport $sport, $year)
    {
        return $this->_em->createQueryBuilder()
            ->select(
                'COUNT(t.id) as num',
                'SUM(t.distance) as total_distance',
                'MIN(t.distance) as min_distance',
                'MAX(t.distance) as max_distance'
            )
            ->from('CoreBundle:Training', 't')
            ->where('t.account = :account')
            ->andWhere('t.sport = :sport')
            ->andWhere('t.time BETWEEN :startTime and :endTime')
            ->setParameters([
                ':account' => $account->getId(),
                ':sport' => $sport->getId(),
                ':startTime' => mktime(0, 0, 0, 1, 1, $year),
                ':endTime' => mktime(23, 59, 59, 12, 31, $year)
            ])
            ->getQuery();
    }

    /**
     * @param Account $account
     * @return bool
     */
    public function accountHasLockedTrainings(Account $account)
    {
        return null !== $this->createQueryBuilder('t')
                ->select('t.id')
                ->setMaxResults(1)
                ->where('t.account = :accountid AND t.lock = 1')
                ->setParameter('accountid', $account->getId())
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * @param Account $account
     * @return bool
     */
    public function latestActivities(Account $account, $limit = 20)
    {
        return $this->createQueryBuilder('t')
                ->select('t')
                ->where('t.account= :accountid')
                ->setParameter('accountid', $account->getId())
                ->orderBy('t.time', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    public function save(Training $training)
    {
        $this->_em->persist($training);
        $this->_em->flush();
    }
}
