<?php
namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
}
