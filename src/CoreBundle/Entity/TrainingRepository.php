<?php
namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TrainingRepository extends EntityRepository
{    
    public function getAmountOfLoggedKilometers($cache = true)
    {
	return $this->createQueryBuilder('t')
		->select('SUM(t.distance)')
		->getQuery()
		->useResultCache($cache)
		->setResultCacheLifetime(120)
		->getSingleScalarResult();
    }
}