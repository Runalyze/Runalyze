<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Service\WeatherForecast\DatabaseCacheInterface;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\Strategy\DatabaseCache;

class WeathercacheRepository extends EntityRepository implements DatabaseCacheInterface
{
    /**
     * @param Location $location
     * @param int $timeTolerance [s]
     *
     * @return WeatherData|null
     */
    public function getCachedWeatherDataFor(Location $location, $timeTolerance)
    {
        if ($location->hasPosition()) {
            $result = $this->createQueryBuilder('w')
                ->select('w')
                ->where('w.geohash LIKE :geohash')
                ->andWhere('w.time BETWEEN :starttime AND :endtime')
                ->setParameter('geohash', substr($location->getGeohash(), 0, Weathercache::GEOHASH_PRECISION_LOOKUP).'%')
                ->setParameter('starttime', $location->getTimestamp() - $timeTolerance)
                ->setParameter('endtime', $location->getTimestamp() + $timeTolerance)
                ->orderBy('w.time', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($result instanceof Weathercache) {
                return $result->getAsWeatherData();
            }
        }

        return null;
    }

    public function cacheWeatherData(WeatherData $data, Location $location)
    {
        if (null === $this->getCachedWeatherDataFor($location, DatabaseCache::TIME_PRECISION)) {
            $cache = new Weathercache();
            $cache->setWeatherData($data);
            $cache->setLocation($location);

            $this->save($cache);
        }
    }

    public function save(Weathercache $cache)
    {
        $this->_em->persist($cache);
        $this->_em->flush($cache);
    }
}
