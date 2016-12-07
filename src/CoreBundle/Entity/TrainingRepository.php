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
}
