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
     * @ORM\Column(name="temperature", type="integer",  precision=4, nullable=true)
     */
    private $temperature;

    /**
     * @var boolean
     *
     * @ORM\Column(name="wind_speed", type="integer", precision=3, nullable=true, options={"unsigned":true})
     */
    private $windSpeed;

    /**
     * @var integer
     *
     * @ORM\Column(name="wind_deg", type="smallint", precision=3, nullable=true, options={"unsigned":true})
     */
    private $windDeg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="humidity", type="integer", precision=3, nullable=true, options={"unsigned":true})
     */
    private $humidity;

    /**
     * @var integer
     *
     * @ORM\Column(name="pressure", type="smallint", precision=4, nullable=true,  options={"unsigned":true})
     */
    private $pressure;

    /**
     * @var integer
     *
     * @ORM\Column(name="weatherid", type="smallint", nullable=false, options={"default":1})
     */
    private $weatherid = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="weather_source", type="integer", precision=2, nullable=true, options={"unsigned":true})
     */
    private $weatherSource;


}

