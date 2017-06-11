<?php

namespace Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb;

class Climb
{
    /** @var int|null */
    protected $TrackdataStartIndex;

    /** @var int|null */
    protected $TrackdataEndIndex;

    /** @var float [km] */
    protected $Distance;

    /** @var int [m] */
    protected $Elevation;

    /** @var int|null [m] */
    protected $AltitudeAtTop;

    /** @var ClimbProfile|null */
    protected $ClimbProfile;

    /**
     * @param float $distance [km]
     * @param int $elevation [m]
     * @param int|null $trackdataStartIndex
     * @param int|null $trackdataEndIndex
     */
    public function __construct($distance, $elevation, $trackdataStartIndex = null, $trackdataEndIndex = null)
    {
        $this->Distance = $distance;
        $this->Elevation = $elevation;
        $this->TrackdataStartIndex = $trackdataStartIndex;
        $this->TrackdataEndIndex = $trackdataEndIndex;
    }

    /**
     * @return int|null
     */
    public function getTrackdataStartIndex()
    {
        return $this->TrackdataStartIndex;
    }

    /**
     * @return bool
     */
    public function knowsTrackdataStartIndex()
    {
        return null !== $this->TrackdataStartIndex;
    }

    /**
     * @return int|null
     */
    public function getTrackdataEndIndex()
    {
        return $this->TrackdataEndIndex;
    }

    /**
     * @return bool
     */
    public function knowsTrackdataEndIndex()
    {
        return null !== $this->TrackdataEndIndex;
    }

    /**
     * @return float [km]
     */
    public function getDistance()
    {
        return $this->Distance;
    }

    /**
     * @return int [m]
     */
    public function getElevation()
    {
        return $this->Elevation;
    }

    /**
     * @return float
     */
    public function getGradient()
    {
        return $this->Elevation / $this->Distance / 1000;
    }

    /**
     * @param int $altitude [m]
     * @return $this
     */
    public function setAltitudeAtTop($altitude)
    {
        $this->AltitudeAtTop = $altitude;

        return $this;
    }

    /**
     * @return int|null [m]
     */
    public function getAltitudeAtTop()
    {
        return $this->AltitudeAtTop;
    }

    /**
     * @return bool
     */
    public function knowsAltitudeAtTop()
    {
        return null !== $this->AltitudeAtTop;
    }

    /**
     * @param ClimbProfile $profile
     * @return $this
     */
    public function setClimbProfile(ClimbProfile $profile)
    {
        $this->ClimbProfile = $profile;

        return $this;
    }

    /**
     * @return null|ClimbProfile
     */
    public function getClimbProfile()
    {
        return $this->ClimbProfile;
    }

    /**
     * @return bool
     */
    public function knowsClimbProfile()
    {
        return null !== $this->ClimbProfile;
    }
}
