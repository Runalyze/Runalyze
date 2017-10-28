<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentRepository;
use Runalyze\Bundle\CoreBundle\Entity\Hrv;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\Swimdata;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Parser\Activity\Common\Data\ActivityData;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\FitDetails;
use Runalyze\Parser\Activity\Common\Data\Metadata;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Profile\Sport\Mapping\EnglishLanguageMapping;
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\Profile\Weather\Mapping\EnglishTextMapping;

class ActivityDataContainerToActivityContextConverter
{
    /** @var SportRepository */
    protected $SportRepository;

    /** @var TypeRepository */
    protected $TypeRepository;

    /** @var EquipmentRepository */
    protected $EquipmentRepository;

    /** @var Account */
    protected $Account;

    public function __construct(
        SportRepository $sportRepository,
        TypeRepository $typeRepository,
        EquipmentRepository $equipmentRepository,
        Account $account
    )
    {
        $this->SportRepository = $sportRepository;
        $this->TypeRepository = $typeRepository;
        $this->EquipmentRepository = $equipmentRepository;
        $this->Account = $account;
    }

    /**
     * @param ActivityDataContainer $container
     * @return ActivityContext
     */
    public function getContextFor(ActivityDataContainer $container)
    {
        $activity = new Training();
        $activity->setAccount($this->Account);

        $this->setActivityDetailsFor($activity, $container);
        $this->setTrackdataFor($activity, $container);
        $this->setSwimdataFor($activity, $container);
        $this->setRouteFor($activity, $container);
        $this->setHrvFor($activity, $container);
        $this->setRaceResultFor($activity, $container);

        return new ActivityContext(
            $activity,
            $activity->getTrackdata(),
            $activity->getSwimdata(),
            $activity->getRoute(),
            $activity->getHrv(),
            $activity->getRaceresult()
        );
    }

    protected function setActivityDetailsFor(Training $activity, ActivityDataContainer $container)
    {
        $this->setActivityMetadataFor($activity, $container->Metadata);
        $this->setActivityDataFor($activity, $container->ActivityData);
        $this->setActivityFitDetailsFor($activity, $container->FitDetails);
        $this->setActivityWeatherDataFor($activity, $container->WeatherData);

        $activity->setSplits($container->Rounds);
    }

    protected function setActivityMetadataFor(Training $activity, Metadata $metadata)
    {
        $activity->setTime($metadata->getTimestamp());
        $activity->setTimezoneOffset($metadata->getTimezoneOffset());

        $activity->setCreator($metadata->getCreator());
        $activity->setCreatorDetails($metadata->getCreatorDetails());

        $activity->setTitle($metadata->getDescription());
        $activity->setNotes($metadata->getNotes());

        $this->tryToSetSportFor($activity, $metadata);
        $this->tryToSetTypeFor($activity, $metadata->getTypeName());
        $this->tryToSetEquipmentFor($activity, $metadata->getEquipmentNames());
    }

    protected function tryToSetSportFor(Training $activity, Metadata $metadata)
    {
        $internalId = $this->getInternalSportIdFrom($metadata);

        if (null !== $internalId) {
            $sport = $this->SportRepository->findInternalIdFor($internalId, $this->Account);

            if (null !== $sport) {
                $activity->setSport($sport);
            }
        }
    }

    protected function getInternalSportIdFrom(Metadata $metadata)
    {
        if (null !== $metadata->getInternalSportId()) {
            return $metadata->getInternalSportId();
        }

        if ('' != $metadata->getSportName()) {
            $internalId = (new EnglishLanguageMapping())->toInternal($metadata->getSportName());

            if (SportProfile::GENERIC != $internalId) {
                return $internalId;
            }
        }

        return null;
    }

    protected function tryToSetTypeFor(Training $activity, $typeName)
    {
        if ('' != $typeName) {
            $type = $this->TypeRepository->findByNameFor($typeName, $this->Account);

            if (null !== $type) {
                $activity->setType($type);
            }
        }
    }

    protected function tryToSetEquipmentFor(Training $activity, array $equipmentNames)
    {
        if (!empty($equipmentNames)) {
            foreach ($this->EquipmentRepository->findByName($equipmentNames, $this->Account) as $equipment) {
                $activity->addEquipment($equipment);
            }
        }
    }

    protected function setActivityDataFor(Training $activity, ActivityData $activityData)
    {
        $activity->setS($activityData->Duration);
        $activity->setElapsedTime($activityData->ElapsedTime);
        $activity->setDistance($activityData->Distance);
        $activity->setElevation($activityData->Elevation);
        $activity->setKcal($activityData->EnergyConsumption);
        $activity->setPower($activityData->AvgPower);
        $activity->setPulseAvg($activityData->AvgHeartRate);
        $activity->setPulseMax($activityData->MaxHeartRate);
        $activity->setCadence($activityData->AvgCadence);
        $activity->setGroundcontact($activityData->AvgGroundContactTime);
        $activity->setGroundcontactBalance($activityData->AvgGroundContactBalance);
        $activity->setVerticalOscillation($activityData->AvgVerticalOscillation);
        $activity->setTotalStrokes($activityData->TotalStrokes);
        $activity->setTrimp($activityData->Trimp);
        $activity->setRpe($activityData->RPE);
    }

    protected function setActivityFitDetailsFor(Training $activity, FitDetails $fitDetails)
    {
        $activity->setFitVO2maxEstimate($fitDetails->VO2maxEstimate);
        $activity->setFitRecoveryTime($fitDetails->RecoveryTime);
        $activity->setFitHrvAnalysis($fitDetails->HrvAnalysis);
        $activity->setFitTrainingEffect($fitDetails->TrainingEffect);
        $activity->setFitPerformanceCondition($fitDetails->PerformanceCondition);
        $activity->setFitPerformanceConditionEnd($fitDetails->PerformanceConditionEnd);
    }

    protected function setActivityWeatherDataFor(Training $activity, WeatherData $weatherData)
    {
        if ($weatherData->isEmpty()) {
            return;
        }

        $activity->setTemperature($weatherData->Temperature);
        $activity->setWindSpeed($weatherData->WindSpeed);
        $activity->setWindDeg($weatherData->WindDirection);
        $activity->setHumidity($weatherData->Humidity);
        $activity->setPressure($weatherData->AirPressure);

        if (null !== $weatherData->InternalConditionId) {
            $activity->setWeatherid($weatherData->InternalConditionId);
        } elseif ('' != $weatherData->Condition) {
            $activity->setWeatherid((new EnglishTextMapping())->toInternal($weatherData->Condition));
        }
    }

    protected function setTrackdataFor(Training $activity, ActivityDataContainer $container)
    {
        $trackData = $this->getTrackdataFor($container);

        if (null !== $trackData) {
            $trackData->setActivity($activity);
            $activity->setTrackdata($trackData);
        }
    }

    /**
     * @param ActivityDataContainer $container
     * @return Trackdata|null
     */
    protected function getTrackdataFor(ActivityDataContainer $container)
    {
        $trackData = new Trackdata();
        $trackData->setAccount($this->Account);
        $trackData->setPauses($container->Pauses);
        $trackData->setTime($container->ContinuousData->Time ?: null);
        $trackData->setDistance($container->ContinuousData->Distance ?: null);
        $trackData->setHeartrate($container->ContinuousData->HeartRate ?: null);
        $trackData->setCadence($container->ContinuousData->Cadence ?: null);
        $trackData->setPower($container->ContinuousData->Power ?: null);
        $trackData->setTemperature($container->ContinuousData->Temperature ?: null);
        $trackData->setGroundcontact($container->ContinuousData->GroundContactTime ?: null);
        $trackData->setVerticalOscillation($container->ContinuousData->VerticalOscillation ?: null);
        $trackData->setGroundcontactBalance($container->ContinuousData->GroundContactBalance ?: null);
        $trackData->setSmo20($container->ContinuousData->MuscleOxygenation ?: null);
        $trackData->setSmo21($container->ContinuousData->MuscleOxygenation_2 ?: null);
        $trackData->setThb0($container->ContinuousData->TotalHaemoglobin ?: null);
        $trackData->setThb1($container->ContinuousData->TotalHaemoglobin_2 ?: null);

        if ($trackData->isEmpty()) {
            return null;
        }

        return $trackData;
    }

    protected function setSwimdataFor(Training $activity, ActivityDataContainer $container)
    {
        $swimData = $this->getSwimdataFor($container);

        if (null !== $swimData) {
            $swimData->setActivity($activity);
            $activity->setSwimdata($swimData);
        }
    }

    /**
     * @param ActivityDataContainer $container
     * @return Swimdata|null
     */
    protected function getSwimdataFor(ActivityDataContainer $container)
    {
        $swimData = new Swimdata();
        $swimData->setAccount($this->Account);
        $swimData->setPoolLength($container->ActivityData->PoolLength ?: 0);
        $swimData->setStroke($container->ContinuousData->Strokes ?: null);
        $swimData->setStroketype($container->ContinuousData->StrokeType ?: null);

        if ($swimData->isEmpty()) {
            return null;
        }

        return $swimData;
    }

    protected function setRouteFor(Training $activity, ActivityDataContainer $container)
    {
        $route = $this->getRouteFor($container);

        if (null !== $route) {
            $activity->setRoute($route);
        }
    }

    /**
     * @param ActivityDataContainer $container
     * @return Route|null
     */
    protected function getRouteFor(ActivityDataContainer $container)
    {
        $route = new Route();
        $route->setAccount($this->Account);
        $route->setDistance($container->ActivityData->Distance ?: 0.0);
        $route->setName($container->Metadata->getRouteDescription());
        $route->setElevation($container->ActivityData->Elevation);
        $route->setElevationUp($container->ActivityData->ElevationAscent);
        $route->setElevationDown($container->ActivityData->ElevationDescent);

        if (!empty($container->ContinuousData->Altitude)) {
            if ($container->ContinuousData->IsAltitudeDataBarometric) {
                $route->setElevationsCorrected($container->ContinuousData->Altitude);
            } else {
                $route->setElevationsOriginal($container->ContinuousData->Altitude);
            }
        }

        if (!empty($container->ContinuousData->Latitude) && !empty($container->ContinuousData->Longitude)) {
            $route->setLatitudesAndLongitudes($container->ContinuousData->Latitude, $container->ContinuousData->Longitude);
        }

        return $route->isEmpty() ? null : $route;
    }

    protected function setHrvFor(Training $activity, ActivityDataContainer $container)
    {
        $hrv = $this->getHrvFor($container);

        if (null !== $hrv) {
            $hrv->setActivity($activity);
            $activity->setHrv($hrv);
        }
    }

    /**
     * @param ActivityDataContainer $container
     * @return Hrv|null
     */
    protected function getHrvFor(ActivityDataContainer $container)
    {
        if (empty($container->RRIntervals)) {
            return null;
        }

        $hrv = new Hrv();
        $hrv->setAccount($this->Account);
        $hrv->setData($container->RRIntervals);

        return $hrv;
    }

    protected function setRaceResultFor(Training $activity, ActivityDataContainer $container)
    {
        $raceResult = $this->getRaceResultFor($container);

        if (null !== $raceResult) {
            $raceResult->setActivity($activity);
            $raceResult->fillFromActivity($activity);
            $activity->setRaceresult($raceResult);
        }
    }

    /**
     * @param ActivityDataContainer $container
     * @return Raceresult|null
     */
    protected function getRaceResultFor(ActivityDataContainer $container)
    {
        return null;
    }
}
