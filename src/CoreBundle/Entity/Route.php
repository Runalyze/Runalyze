<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Account;

/**
 * Route
 *
 * @ORM\Table(name="route", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Route
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", precision=10, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="cities", type="string", length=255, nullable=false)
     */
    private $cities = '';

    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="decimal", precision=6, scale=2, nullable=false, options={"unsigned":true})
     */
    private $distance = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevation = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation_up", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevationUp = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation_down", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevationDown = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="geohashes", type="text", nullable=true)
     */
    private $geohashes;

    /**
     * @var string
     *
     * @ORM\Column(name="elevations_original", type="text", nullable=true)
     */
    private $elevationsOriginal;

    /**
     * @var string
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
     * @var string
     *
     * @ORM\Column(name="startpoint", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $startpoint;

    /**
     * @var string
     *
     * @ORM\Column(name="endpoint", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $endpoint;

    /**
     * @var string
     *
     * @ORM\Column(name="min", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $min;

    /**
     * @var string
     *
     * @ORM\Column(name="max", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $max;

    /**
     * @var boolean
     *
     * @ORM\Column(name="in_routenet", type="boolean", nullable=false, options={"default":0})
     */
    private $inRoutenet = '0';

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $account;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Route
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cities
     *
     * @param string $cities
     *
     * @return Route
     */
    public function setCities($cities)
    {
        $this->cities = $cities;

        return $this;
    }

    /**
     * Get cities
     *
     * @return string
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * Set distance
     *
     * @param string $distance
     *
     * @return Route
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return string
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set elevation
     *
     * @param string $elevation
     *
     * @return Route
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Get elevation
     *
     * @return string
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set elevationUp
     *
     * @param string $elevationUp
     *
     * @return Route
     */
    public function setElevationUp($elevationUp)
    {
        $this->elevationUp = $elevationUp;

        return $this;
    }

    /**
     * Get elevationUp
     *
     * @return string
     */
    public function getElevationUp()
    {
        return $this->elevationUp;
    }

    /**
     * Set elevationDown
     *
     * @param string $elevationDown
     *
     * @return Route
     */
    public function setElevationDown($elevationDown)
    {
        $this->elevationDown = $elevationDown;

        return $this;
    }

    /**
     * Get elevationDown
     *
     * @return string
     */
    public function getElevationDown()
    {
        return $this->elevationDown;
    }

    /**
     * Set geohashes
     *
     * @param string $geohashes
     *
     * @return Route
     */
    public function setGeohashes($geohashes)
    {
        $this->geohashes = $geohashes;

        return $this;
    }

    /**
     * Get geohashes
     *
     * @return string
     */
    public function getGeohashes()
    {
        return $this->geohashes;
    }

    /**
     * Set elevationsOriginal
     *
     * @param string $elevationsOriginal
     *
     * @return Route
     */
    public function setElevationsOriginal($elevationsOriginal)
    {
        $this->elevationsOriginal = $elevationsOriginal;

        return $this;
    }

    /**
     * Get elevationsOriginal
     *
     * @return string
     */
    public function getElevationsOriginal()
    {
        return $this->elevationsOriginal;
    }

    /**
     * Set elevationsCorrected
     *
     * @param string $elevationsCorrected
     *
     * @return Route
     */
    public function setElevationsCorrected($elevationsCorrected)
    {
        $this->elevationsCorrected = $elevationsCorrected;

        return $this;
    }

    /**
     * Get elevationsCorrected
     *
     * @return string
     */
    public function getElevationsCorrected()
    {
        return $this->elevationsCorrected;
    }

    /**
     * Set elevationsSource
     *
     * @param string $elevationsSource
     *
     * @return Route
     */
    public function setElevationsSource($elevationsSource)
    {
        $this->elevationsSource = $elevationsSource;

        return $this;
    }

    /**
     * Get elevationsSource
     *
     * @return string
     */
    public function getElevationsSource()
    {
        return $this->elevationsSource;
    }

    /**
     * Set startpoint
     *
     * @param string $startpoint
     *
     * @return Route
     */
    public function setStartpoint($startpoint)
    {
        $this->startpoint = $startpoint;

        return $this;
    }

    /**
     * Get startpoint
     *
     * @return string
     */
    public function getStartpoint()
    {
        return $this->startpoint;
    }

    /**
     * Set endpoint
     *
     * @param string $endpoint
     *
     * @return Route
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set min
     *
     * @param string $min
     *
     * @return Route
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get min
     *
     * @return string
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set max
     *
     * @param string $max
     *
     * @return Route
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get max
     *
     * @return string
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Set inRoutenet
     *
     * @param string $inRoutenet
     *
     * @return Route
     */
    public function setInRoutenet($inRoutenet)
    {
        $this->inRoutenet = $inRoutenet;

        return $this;
    }

    /**
     * Get inRoutenet
     *
     * @return string
     */
    public function getInRoutenet()
    {
        return $this->inRoutenet;
    }

    /**
     * Set account
     *
     * @param Account|null $account
     *
     * @return Route
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account|null
     */
    public function getAccount()
    {
        return $this->account;
    }
}

