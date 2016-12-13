<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RouteRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Route[]
     */
    public function accountHasLockedRoutes(Account $account)
    {
        return $this->createQueryBuilder('r')
            ->select('r.id')
            ->setMaxResults(1)
            ->where('r.account = :accountid AND r.lock = 1')
            ->setParameter('accountid', $account->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Route $route)
    {
        $this->_em->persist($route);
        $this->_em->flush();
    }
}
