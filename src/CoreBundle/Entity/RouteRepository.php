<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use League\Geotools\Geohash\Geohash;

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

    /**
     * @param int $routeId
     * @return \League\Geotools\Coordinate\CoordinateInterface|null
     */
    public function getStartCoordinatesFor($routeId)
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.startpoint')
            ->setMaxResults(1)
            ->where('r.id = :routeid')
            ->setParameter('routeid', $routeId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        if (null !== $result) {
            return (new Geohash())->decode($result['startpoint'])->getCoordinate();
        }

        return null;
    }

    public function save(Route $route)
    {
        $this->_em->persist($route);
        $this->_em->flush();
    }
}
