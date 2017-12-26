<?php

namespace Runalyze\Parser\Activity\Bridge;

use Runalyze\Model\Route;
use Runalyze\Model\Swimdata;
use Runalyze\Model\Trackdata;
use Runalyze\Parser\Activity\Common\Data\ContinuousData;

class ContinuousDataConverter
{
    /** @var ContinuousData */
    protected $Data;

    public function __construct(ContinuousData $data)
    {
        $this->Data = $data;
    }

    /**
     * @return Trackdata\Entity
     */
    public function convertToLegacyTrackdataModel()
    {
        return new Trackdata\Entity([
            Trackdata\Entity::TIME => $this->Data->Time,
            Trackdata\Entity::HEARTRATE => $this->Data->HeartRate,
            Trackdata\Entity::CADENCE => $this->Data->Cadence,
            Trackdata\Entity::POWER => $this->Data->Power,
            Trackdata\Entity::TEMPERATURE => $this->Data->Temperature,
            Trackdata\Entity::GROUNDCONTACT => $this->Data->GroundContactTime,
            Trackdata\Entity::VERTICAL_OSCILLATION => $this->Data->VerticalOscillation,
            Trackdata\Entity::GROUNDCONTACT_BALANCE => $this->Data->GroundContactBalance,
            Trackdata\Entity::SMO2_0 => $this->Data->MuscleOxygenation,
            Trackdata\Entity::SMO2_1 => $this->Data->MuscleOxygenation_2,
            Trackdata\Entity::THB_0 => $this->Data->TotalHaemoglobin,
            Trackdata\Entity::THB_1 => $this->Data->TotalHaemoglobin_2
        ]);
    }

    /**
     * @return Route\Entity
     */
    public function convertToLegacyRouteModel()
    {
        $route = new Route\Entity([
            Route\Entity::DISTANCE => $this->Data->Distance,
            Route\Entity::ELEVATIONS_ORIGINAL => $this->Data->Altitude
        ]);
        $route->setLatitudesLongitudes($this->Data->Latitude, $this->Data->Longitude);

        return $route;
    }

    /**
     * @return Swimdata\Entity
     */
    public function convertToLegacySwimdataModel()
    {
        return new Swimdata\Entity([
            Swimdata\Entity::STROKE => $this->Data->Strokes,
            Swimdata\Entity::STROKETYPE => $this->Data->StrokeType
        ]);
    }
}
