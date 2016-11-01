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
     * @ORM\Column(name="geohash", type="string", length=5, nullable=false, options={"fixed" = true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $geohash = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", precision=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $time;

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
     * @ORM\Column(name="humidity", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $humidity;

    /**
     * @var integer
     *
     * @ORM\Column(name="pressure", type="smallint", precision=3, nullable=true,  options={"unsigned":true})
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
     * @ORM\Column(name="weather_source", columnDefinition="TINYINT(2) unsigned DEFAULT NULL")
     */
    private $weatherSource;

    /**
     * Set geohash
     *
     * @param string $geohash
     *
     * @return Weathercache
     */
    public function setGeohash($geohash)
    {
        $this->geohash = $geohash;

        return $this;
    }

    /**
     * Get geohash
     *
     * @return string
     */
    public function getGeohash()
    {
        return $this->geohash;
    }

    /**
     * Set time
     *
     * @param string $time
     *
     * @return Weathercache
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set temperature
     *
     * @param string $temperature
     *
     * @return Weathercache
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Get temperature
     *
     * @return string
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Set windSpeed
     *
     * @param string $windSpeed
     *
     * @return Weathercache
     */
    public function setWindSpeed($windSpeed)
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    /**
     * Get windSpeed
     *
     * @return string
     */
    public function getWindSpeed()
    {
        return $this->windSpeed;
    }

    /**
     * Set windDeg
     *
     * @param string $windDeg
     *
     * @return Weathercache
     */
    public function setWindDeg($windDeg)
    {
        $this->windDeg = $windDeg;

        return $this;
    }

    /**
     * Get windDeg
     *
     * @return string
     */
    public function getWindDeg()
    {
        return $this->windDeg;
    }

    /**
     * Set humidity
     *
     * @param string $humidity
     *
     * @return Weathercache
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;

        return $this;
    }

    /**
     * Get humidity
     *
     * @return string
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * Set pressure
     *
     * @param string $pressure
     *
     * @return Weathercache
     */
    public function setPressure($pressure)
    {
        $this->pressure = $pressure;

        return $this;
    }

    /**
     * Get pressure
     *
     * @return string
     */
    public function getPressure()
    {
        return $this->pressure;
    }

    /**
     * Set weatherid
     *
     * @param string $weatherid
     *
     * @return Weathercache
     */
    public function setWeatherid($weatherid)
    {
        $this->weatherid = $weatherid;

        return $this;
    }

    /**
     * Get weatherid
     *
     * @return string
     */
    public function getWeatherid()
    {
        return $this->weatherid;
    }

    /**
     * Set weatherSource
     *
     * @param string $weatherSource
     *
     * @return Weathercache
     */
    public function setWeatherSource($weatherSource)
    {
        $this->weatherSource = $weatherSource;

        return $this;
    }

    /**
     * Get weatherSource
     *
     * @return string
     */
    public function getWeatherSource()
    {
        return $this->weatherSource;
    }
}

