<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Service\WeatherForecast\Location;

/**
 * Weathercache
 *
 * @ORM\Table(name="weathercache")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\WeathercacheRepository")
 */
class Weathercache
{
    /** @var int */
    const GEOHASH_PRECISION_DATABASE = 5;

    /** @var int */
    const GEOHASH_PRECISION_LOOKUP = 4;

    /**
     * @var string
     *
     * @ORM\Column(name="geohash", type="string", length=5, nullable=false, options={"fixed" = true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $geohash = '';

    /**
     * @var int
     *
     * @ORM\Column(name="time", type="integer", precision=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $time;

    /**
     * @var int|null [°C]
     *
     * @ORM\Column(name="temperature", type="tinyint", nullable=true)
     */
    private $temperature;

    /**
     * @var int|null [km/h]
     *
     * @ORM\Column(name="wind_speed", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $windSpeed;

    /**
     * @var int|null [°]
     *
     * @ORM\Column(name="wind_deg", type="smallint", precision=3, nullable=true, options={"unsigned":true})
     */
    private $windDeg;

    /**
     * @var int|null [%]
     *
     * @ORM\Column(name="humidity", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $humidity;

    /**
     * @var int [hPa]
     *
     * @ORM\Column(name="pressure", type="smallint", precision=4, nullable=true, options={"unsigned":true})
     */
    private $pressure;

    /**
     * @var int
     *
     * @ORM\Column(name="weatherid", type="smallint", nullable=false)
     */
    private $weatherid = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="weather_source", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $weatherSource;

    /**
     * @param string $geohash
     *
     * @return $this
     */
    public function setGeohash($geohash)
    {
        $this->geohash = substr($geohash, 0, self::GEOHASH_PRECISION_DATABASE);

        return $this;
    }

    /**
     * @return string
     */
    public function getGeohash()
    {
        return $this->geohash;
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int|null $temperature [°C]
     *
     * @return $this
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * @return int|null [°C]
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * @param int|null $windSpeed [km/h]
     *
     * @return $this
     */
    public function setWindSpeed($windSpeed)
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    /**
     * @return int|null [km/h]
     */
    public function getWindSpeed()
    {
        return $this->windSpeed;
    }

    /**
     * @param int|null $windDeg [°]
     *
     * @return $this
     */
    public function setWindDeg($windDeg)
    {
        $this->windDeg = $windDeg;

        return $this;
    }

    /**
     * @return int|null [°]
     */
    public function getWindDeg()
    {
        return $this->windDeg;
    }

    /**
     * @param int|null $humidity [%]
     *
     * @return $this
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;

        return $this;
    }

    /**
     * @return int|null [%]
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * @param int|null $pressure [hPa]
     *
     * @return $this
     */
    public function setPressure($pressure)
    {
        $this->pressure = $pressure;

        return $this;
    }

    /**
     * @return int|null [hPa]
     */
    public function getPressure()
    {
        return $this->pressure;
    }

    /**
     * @param int $weatherid
     *
     * @return $this
     */
    public function setWeatherid($weatherid)
    {
        $this->weatherid = $weatherid;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeatherid()
    {
        return $this->weatherid;
    }

    /**
     * @param int|null $weatherSource
     *
     * @return $this
     */
    public function setWeatherSource($weatherSource)
    {
        $this->weatherSource = $weatherSource;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeatherSource()
    {
        return $this->weatherSource;
    }

    /**
     * @return WeatherData
     */
    public function getAsWeatherData()
    {
        $data = new WeatherData();
        $data->InternalConditionId = $this->weatherid;
        $data->Temperature = $this->temperature;
        $data->AirPressure = $this->pressure;
        $data->Humidity = $this->humidity;
        $data->WindSpeed = $this->windSpeed;
        $data->WindDirection = $this->windDeg;
        $data->Source = $this->weatherSource;

        return $data;
    }

    public function setWeatherData(WeatherData $data)
    {
        $this->weatherid = $data->InternalConditionId;
        $this->temperature = $data->Temperature;
        $this->pressure = $data->AirPressure;
        $this->humidity = $data->Humidity;
        $this->windSpeed = $data->WindSpeed;
        $this->windDeg = $data->WindDirection;
        $this->weatherSource = $data->Source;
    }

    public function setLocation(Location $location)
    {
        $this->setGeohash($location->getGeohash());
        $this->setTime($location->getTimestamp());
    }
}
