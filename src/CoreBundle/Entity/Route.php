<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Account;

/**
 * Route
 *
 * @ORM\Table(name="route", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\RouteRepository")
 */
class Route
{
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
     * @var float
     *
     * @ORM\Column(name="distance", columnDefinition="decimal(6,2) unsigned NOT NULL DEFAULT '0.00'")
     */
    private $distance = '0.00';

    /**
     * @var int
     *
     * @ORM\Column(name="elevation", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevation = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="elevation_up", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevationUp = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="elevation_down", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevationDown = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(name="geohashes", type="text", nullable=true)
     */
    private $geohashes;

    /**
     * @var string|null
     *
     * @ORM\Column(name="elevations_original", type="text", nullable=true)
     */
    private $elevationsOriginal;

    /**
     * @var string|null
     *
     * @ORM\Column(name="elevations_corrected", type="text", nullable=true)
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
     * @ORM\Column(name="in_routenet", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
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
     * @ORM\Column(name="`lock`", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $lock = false;

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
     * @param float $distance
     *
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param int $elevation
     *
     * @return $this
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * @return int
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * @param int $elevationUp
     *
     * @return $this
     */
    public function setElevationUp($elevationUp)
    {
        $this->elevationUp = $elevationUp;

        return $this;
    }

    /**
     * @return int
     */
    public function getElevationUp()
    {
        return $this->elevationUp;
    }

    /**
     * @param int $elevationDown
     *
     * @return $this
     */
    public function setElevationDown($elevationDown)
    {
        $this->elevationDown = $elevationDown;

        return $this;
    }

    /**
     * @return int
     */
    public function getElevationDown()
    {
        return $this->elevationDown;
    }

    /**
     * @param string|null $geohashes
     *
     * @return $this
     */
    public function setGeohashes($geohashes)
    {
        $this->geohashes = $geohashes;

        return $this;
    }

    /**
     * @return string|null
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
     * @param string|null $elevationsOriginal
     *
     * @return $this
     */
    public function setElevationsOriginal($elevationsOriginal)
    {
        $this->elevationsOriginal = $elevationsOriginal;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getElevationsOriginal()
    {
        return $this->elevationsOriginal;
    }

    /**
     * @param string|null $elevationsCorrected
     *
     * @return $this
     */
    public function setElevationsCorrected($elevationsCorrected)
    {
        $this->elevationsCorrected = $elevationsCorrected;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getElevationsCorrected()
    {
        return $this->elevationsCorrected;
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
     * @param bool $inRoutenet
     *
     * @return $this
     */
    public function setInRoutenet($inRoutenet)
    {
        $this->inRoutenet = (bool)$inRoutenet;

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
     * @param bool $lock
     *
     * @return $this
     */
    public function setLock($lock)
    {
        $this->lock = (bool)$lock;

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
}

