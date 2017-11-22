<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geohash\Geohash;
use Runalyze\Bundle\CoreBundle\Entity\Adapter\RouteAdapter;
use Runalyze\Calculation\Route\GeohashLine;

/**
 * Route
 *
 * @ORM\Table(name="route")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\RouteRepository")
 * @ORM\EntityListeners({"Runalyze\Bundle\CoreBundle\EntityListener\RouteListener"})
 * @ORM\HasLifecycleCallbacks()
 */
class Route
{
    /** @var int */
    const PATH_GEOHASH_PRECISION = 12;

    /** @var int */
    const BOUNDARIES_GEOHASH_PRECISION = 10;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=10, nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, options={"default":""})
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="cities", type="string", length=255, nullable=false, options={"default":""})
     */
    private $cities = '';

    /**
     * @var float [km]
     *
     * @ORM\Column(name="distance", type="decimal", precision=6, scale=2, options={"unsigned":true})
     */
    private $distance = 0.0;

    /**
     * @var int [m]
     *
     * @ORM\Column(name="elevation", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $elevation = 0;

    /**
     * @var int [m]
     *
     * @ORM\Column(name="elevation_up", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $elevationUp = 0;

    /**
     * @var int [m]
     *
     * @ORM\Column(name="elevation_down", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $elevationDown = 0;

    /**
     * @var array|null
     *
     * @ORM\Column(name="geohashes", type="geohash_array", nullable=true)
     */
    private $geohashes;

    /**
     * @var array|null [m]
     *
     * @ORM\Column(name="elevations_original", type="pipe_array", nullable=true)
     */
    private $elevationsOriginal;

    /**
     * @var array|null [m]
     *
     * @ORM\Column(name="elevations_corrected", type="pipe_array", nullable=true)
     */
    private $elevationsCorrected;

    /**
     * @var string
     *
     * @ORM\Column(name="elevations_source", type="string", length=255, nullable=false, options={"default" = ""})
     */
    private $elevationsSource = '';

    /**
     * @var string|null [geohash]
     *
     * @ORM\Column(name="startpoint", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $startpoint;

    /**
     * @var string|null [geohash]
     *
     * @ORM\Column(name="endpoint", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $endpoint;

    /**
     * @var string|null [geohash]
     *
     * @ORM\Column(name="min", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $min;

    /**
     * @var string|null [geohash]
     *
     * @ORM\Column(name="max", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $max;

    /**
     * @var bool
     *
     * @ORM\Column(name="in_routenet", type="boolean")
     */
    private $inRoutenet = false;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var bool
     *
     * @ORM\Column(name="`lock`", type="boolean")
     */
    private $lock = false;

    /** @var bool */
    private $areMinMaxSynchronized = true;

    /** @var RouteAdapter */
    private $Adapter;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $cities
     *
     * @return $this
     */
    public function setCities($cities)
    {
        $this->cities = $cities;

        return $this;
    }

    /**
     * @return string
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param float $distance [km]
     *
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return float [km]
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param int $elevation [m]
     *
     * @return $this
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * @return int [m]
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * @param int $elevationUp [m]
     *
     * @return $this
     */
    public function setElevationUp($elevationUp)
    {
        $this->elevationUp = $elevationUp;

        return $this;
    }

    /**
     * @return int [m]
     */
    public function getElevationUp()
    {
        return $this->elevationUp;
    }

    /**
     * @param int $elevationDown [m]
     *
     * @return $this
     */
    public function setElevationDown($elevationDown)
    {
        $this->elevationDown = $elevationDown;

        return $this;
    }

    /**
     * @return int [m]
     */
    public function getElevationDown()
    {
        return $this->elevationDown;
    }

    /**
     * @param array|null $geohashes
     *
     * @return $this
     */
    public function setGeohashes(array $geohashes = null)
    {
        $this->areMinMaxSynchronized = false;
        $this->geohashes = $geohashes;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getGeohashes()
    {
        return $this->geohashes;
    }

    /**
     * @return bool
     */
    public function hasGeohashes()
    {
        return null !== $this->geohashes;
    }

    /**
     * @param array $latitudes
     * @param array $longitudes
     *
     * @return $this
     */
    public function setLatitudesAndLongitudes(array $latitudes, array $longitudes)
    {
        $size = count($latitudes);

        if ($size != count($longitudes)) {
            throw new \InvalidArgumentException('Latitude and longitude array must be of same length.');
        }

        $latitudes = array_map(function ($value) { return ($value == '') ? 0.0 : (double)$value; }, $latitudes);
        $longitudes = array_map(function ($value) { return ($value == '') ? 0.0 : (double)$value; }, $longitudes);

        $this->areMinMaxSynchronized = false;
        $this->geohashes = [];;

        for ($i = 0; $i < $size; ++$i) {
            $this->geohashes[] = (new Geohash())->encode(
                new Coordinate([$latitudes[$i], $longitudes[$i]]),
                self::PATH_GEOHASH_PRECISION
            )->getGeohash();
        }

        return $this;
    }

    /**
     * @return array [[lat1, lat2, ...], [lng1, lng2, ...]]
     */
    public function getLatitudesAndLongitudes()
    {
        $coordinates = [[], []];

        if (null !== $this->geohashes) {
            $size = count($this->geohashes);

            for ($i = 0; $i < $size; $i++) {
                $coordinate = (new Geohash())->decode($this->geohashes[$i])->getCoordinate();
                $coordinates[0][] = round($coordinate->getLatitude(), 6);
                $coordinates[1][] = round($coordinate->getLongitude(), 6);
            }
        }

        return $coordinates;
    }

    /**
     * @return array
     */
    public function getLatitudes()
    {
        return $this->getLatitudesAndLongitudes()[0];
    }

    /**
     * @return array
     */
    public function getLongitudes()
    {
        return $this->getLatitudesAndLongitudes()[1];
    }

    /**
     * @param array|null $elevationsOriginal [m]
     *
     * @return $this
     */
    public function setElevationsOriginal(array $elevationsOriginal = null)
    {
        $this->elevationsOriginal = $elevationsOriginal;

        return $this;
    }

    /**
     * @return array|null [m]
     */
    public function getElevationsOriginal()
    {
        return $this->elevationsOriginal;
    }

    /**
     * @param array|null $elevationsCorrected [m]
     *
     * @return $this
     */
    public function setElevationsCorrected(array $elevationsCorrected = null)
    {
        $this->elevationsCorrected = $elevationsCorrected;

        return $this;
    }

    /**
     * @return array|null [m]
     */
    public function getElevationsCorrected()
    {
        return $this->elevationsCorrected;
    }

    /**
     * @return array|null [m]
     */
    public function getElevations()
    {
        if (null !== $this->elevationsCorrected) {
            return $this->elevationsCorrected;
        }

        return $this->elevationsOriginal;
    }

    /**
     * @return bool
     */
    public function hasElevations()
    {
        return null !== $this->elevationsCorrected || null !== $this->elevationsOriginal;
    }

    /**
     * @param string|null $elevationsSource
     *
     * @return $this
     */
    public function setElevationsSource($elevationsSource)
    {
        $this->elevationsSource = $elevationsSource;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getElevationsSource()
    {
        return $this->elevationsSource;
    }

    /**
     * @param string|null $startpoint [geohash]
     *
     * @return $this
     */
    public function setStartpoint($startpoint)
    {
        $this->startpoint = $startpoint;

        return $this;
    }

    /**
     * @return string|null [geohash]
     */
    public function getStartpoint()
    {
        return $this->startpoint;
    }

    /**
     * @param string|null $endpoint [geohash]
     *
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return string|null [geohash]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string|null $min [geohash]
     *
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return string|null [geohash]
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param string|null $max [geohash]
     *
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return string|null [geohash]
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function setInRoutenet($flag)
    {
        $this->inRoutenet = (bool)$flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getInRoutenet()
    {
        return $this->inRoutenet;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function setLock($flag)
    {
        $this->lock = (bool)$flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->lock;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (
            '' == $this->name &&
            '' == $this->cities &&
            (null === $this->geohashes || empty($this->geohashes)) &&
            (null === $this->elevationsOriginal || empty($this->elevationsOriginal)) &&
            (null === $this->elevationsCorrected || empty($this->elevationsCorrected))
        );
    }

    /**
     * @return bool
     */
    public function areMinMaxGeohashSynchronized()
    {
        return $this->areMinMaxSynchronized;
    }

    /**
     * @return RouteAdapter
     */
    public function getAdapter()
    {
        if (null === $this->Adapter) {
            $this->Adapter = new RouteAdapter($this);
        }

        return $this->Adapter;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function synchronize()
    {
        $this->setStartEndGeohashes();
        $this->synchronizeMinMaxGeohashIfRequired();

        if (null === $this->elevationsCorrected) {
            $this->elevationsSource = '';
        }
    }

    public function synchronizeMinMaxGeohashIfRequired()
    {
        if (!$this->areMinMaxSynchronized) {
            $this->setMinMaxGeohashes();
        }
    }

    public function setMinMaxGeohashes()
    {
        $this->min = null;
        $this->max = null;

        $coordinates = $this->getLatitudesAndLongitudes();
        $latitudes = array_filter($coordinates[0]);
        $longitudes = array_filter($coordinates[1]);

        if (!empty($latitudes) && !empty($longitudes)) {
            $minCoordinate = new Coordinate([min($latitudes), min($longitudes)]);
            $maxCoordinate = new Coordinate([max($latitudes), max($longitudes)]);

            $this->min = (new Geohash())->encode($minCoordinate, self::BOUNDARIES_GEOHASH_PRECISION)->getGeohash();
            $this->max = (new Geohash())->encode($maxCoordinate, self::BOUNDARIES_GEOHASH_PRECISION)->getGeohash();
        }

        $this->areMinMaxSynchronized = true;
    }

    public function setStartEndGeohashes()
    {
        if (null !== $this->geohashes) {
            $this->startpoint = GeohashLine::findFirstNonNullGeohash($this->geohashes, self::BOUNDARIES_GEOHASH_PRECISION);
            $this->endpoint = GeohashLine::findFirstNonNullGeohash(array_reverse($this->geohashes), self::BOUNDARIES_GEOHASH_PRECISION);

            if (null === $this->startpoint) {
                $this->geohashes = null;
            }
        } else {
            $this->startpoint = null;
            $this->endpoint = null;
        }
    }
}
