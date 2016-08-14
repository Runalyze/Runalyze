<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Weathercache
 *
 * @ORM\Table(name="weathercache")
 * @ORM\Entity
 */
class Weathercache
{
    /**
     * @var string
     *
     * @ORM\Column(name="geohash", type="string", length=5, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $geohash = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $time = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="temperature", type="boolean", nullable=true)
     */
    private $temperature;

    /**
     * @var boolean
     *
     * @ORM\Column(name="wind_speed", type="boolean", nullable=true)
     */
    private $windSpeed;

    /**
     * @var integer
     *
     * @ORM\Column(name="wind_deg", type="smallint", nullable=true)
     */
    private $windDeg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="humidity", type="boolean", nullable=true)
     */
    private $humidity;

    /**
     * @var integer
     *
     * @ORM\Column(name="pressure", type="smallint", nullable=true)
     */
    private $pressure;

    /**
     * @var integer
     *
     * @ORM\Column(name="weatherid", type="smallint", nullable=false)
     */
    private $weatherid = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="weather_source", type="boolean", nullable=true)
     */
    private $weatherSource;


}

