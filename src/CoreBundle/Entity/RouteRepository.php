<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class RouteRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return bool
     */
    public function accountHasLockedRoutes(Account $account)
    {
        return null !== $this->createQueryBuilder('r')
            ->select('r.id')
            ->setMaxResults(1)
            ->where('r.account = :accountid AND r.lock = 1')
            ->setParameter('accountid', $account->getId())
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
    }

    public function save(Route $route)
    {
        $this->_em->persist($route);
        $this->_em->flush();
    }
}
