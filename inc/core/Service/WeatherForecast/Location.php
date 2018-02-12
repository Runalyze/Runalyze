<?php

namespace Runalyze\Service\WeatherForecast;

use League\Geotools\Geohash\Geohash;
use League\Geotools\Coordinate\Coordinate;

class Location
{
    /** @var float|null */
    protected $Latitude;

    /** @var float|null */
    protected $Longitude;

    /** @var \DateTime|null */
    protected $DateTime;

    /** @var string */
    protected $LocationName = '';

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function setPosition($latitude, $longitude)
    {
        $this->Latitude = $latitude;
        $this->Longitude = $longitude;
    }

    /**
     * @param string|mixed $geohash
     */
    public function setGeohash($geohash)
    {
        try {
            $decoded = (new Geohash)->decode($geohash)->getCoordinate();

            $this->Latitude = $decoded->getLatitude();
            $this->Longitude = $decoded->getLongitude();
        } catch (\InvalidArgumentException $e) {
            $this->Latitude = null;
            $this->Longitude = null;
        }
    }

    public function setDateTime(\DateTime $dateTime = null)
    {
        $this->DateTime = $dateTime;
    }

    /**
     * @param string $location
     */
    public function setLocationName($location)
    {
        $this->LocationName = $location;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->Latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->Longitude;
    }

    /**
     * @return Coordinate|null
     */
    public function getCoordinate()
    {
        if ($this->hasPosition()) {
            return new Coordinate(array((float)$this->getLatitude(), (float)$this->getLongitude()));
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getGeohash()
    {
        if ($this->hasPosition()) {
            return (new Geohash)->encode($this->getCoordinate(), 12)->getGeohash();
        }

        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTime()
    {
        return $this->DateTime;
    }

    /**
     * @return int|null
     */
    public function getTimestamp()
    {
        if (null === $this->DateTime) {
            return null;
        }

        return $this->DateTime->getTimestamp();
    }

    /**
     * @return string
     */
    public function getLocationName()
    {
        return $this->LocationName;
    }

    /**
     * Is position set?
     * @return bool
     */
    public function hasPosition()
    {
        return (
            null !== $this->Latitude &&
            null !== $this->Longitude &&
            ($this->Latitude != 0.0 || $this->Longitude != 0.0)
        );
    }

    /**
     * @return bool
     */
    public function hasLocationName()
    {
        return strlen($this->LocationName) > 0;
    }

    /**
     * @return bool
     */
    public function hasDateTime()
    {
        return null !== $this->DateTime;
    }

    /**
     * @param int $seconds
     * @return bool
     */
    public function isOlderThan($seconds = 86400)
    {
        return $this->hasDateTime() && ($this->getTimestamp() < time() - $seconds);
    }
}
