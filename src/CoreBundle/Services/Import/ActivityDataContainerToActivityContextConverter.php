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
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
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
    /** @var int number of decimals */
    const DISTANCE_PRECISION_FOR_ACTIVITY_DATA = 2;

    /** @var int number of decimals */
    const DISTANCE_PRECISION_FOR_TRACK_DATA = 5;

    /** @var SportRepository */
    protected $SportRepository;

    /** @var TypeRepository */
    protected $TypeRepository;

    /** @var EquipmentRepository */
    protected $EquipmentRepository;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var Account */
    protected $Account;

    public function __construct(
        SportRepository $sportRepository,
        TypeRepository $typeRepository,
        EquipmentRepository $equipmentRepository,
        ConfigurationManager $configurationManager,
        Account $account = null
    )
    {
        $this->SportRepository = $sportRepository;
        $this->TypeRepository = $typeRepository;
        $this->EquipmentRepository = $equipmentRepository;
        $this->ConfigurationManager = $configurationManager;
        $this->Account = $account;
    }

    public function setAccount(Account $account)
    {
        $this->Account = $account;
    }

    /**
     * @param ActivityDataContainer $container
     * @param Account $account
     * @return Training
     */
    public function getActivityFor(ActivityDataContainer $container, Account $account = null)
    {
        if (null !== $account) {
            $this->Account = $account;
        }

        $activity = new Training();
        $activity->setAccount($this->Account);

        $this->setActivityDetailsFor($activity, $container);
        $this->setTrackdataFor($activity, $container);
        $this->setSwimdataFor($activity, $container);
        $this->setRouteFor($activity, $container);
        $this->setHrvFor($activity, $container);
        $this->setRaceResultFor($activity, $container);

        return $activity;
    }

    /**
     * @param ActivityDataContainer $container
     * @param Account $account
     * @return ActivityContext
     */
    public function getContextFor(ActivityDataContainer $container, Account $account = null)
    {
        $activity = $this->getActivityFor($container, $account);

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

        $container->Rounds->roundDurations();

        $activity->setSplits($container->Rounds);
    }

    protected function setActivityMetadataFor(Training $activity, Metadata $metadata)
    {
        $activity->setTime($metadata->getTimestamp());
        $activity->setTimezoneOffset($metadata->getTimezoneOffset());
        $activity->setActivityId($metadata->getActivityId());

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
        $sport = null;
        $internalId = $this->getInternalSportIdFrom($metadata);

        if (null !== $internalId) {
            $sport = $this->SportRepository->findInternalIdFor($internalId, $this->Account);
        }

        if (null === $sport) {
            $sport = $this->SportRepository->findThisOrAny(
                $this->ConfigurationManager->getList($this->Account)->getGeneral()->getMainSport(),
                $this->Account
            );
        }

        if (null !== $sport) {
            $activity->setSport($sport);
            $activity->setPublic(!$sport->getDefaultPrivacy());

            if (null !== $sport->getDefaultType()) {
                $activity->setType($sport->getDefaultType());
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
        $activity->setDistance($this->getRoundedValue($activityData->Distance, self::DISTANCE_PRECISION_FOR_ACTIVITY_DATA));
        $activity->setElevation($activityData->Elevation);
        $activity->setKcal($activityData->EnergyConsumption);
        $activity->setPower($this->getRoundedValue($activityData->AvgPower));
        $activity->setPulseAvg($this->getRoundedValue($activityData->AvgHeartRate));
        $activity->setPulseMax($activityData->MaxHeartRate);
        $activity->setCadence($this->getRoundedValue($activityData->AvgCadence));
        $activity->setGroundcontact($this->getRoundedValue($activityData->AvgGroundContactTime));
        $activity->setGroundcontactBalance($this->getRoundedValue($activityData->AvgGroundContactBalance));
        $activity->setVerticalOscillation($this->getRoundedValue($activityData->AvgVerticalOscillation));
        $activity->setAvgImpactGsLeft($this->getRoundedValue($activityData->AvgImpactGsLeft, 1));
        $activity->setAvgImpactGsRight($this->getRoundedValue($activityData->AvgImpactGsRight, 1));
        $activity->setAvgBrakingGsLeft($this->getRoundedValue($activityData->AvgBrakingGsLeft, 1));
        $activity->setAvgBrakingGsRight($this->getRoundedValue($activityData->AvgBrakingGsRight, 1));
        $activity->setAvgFootstrikeTypeLeft($this->getRoundedValue($activityData->AvgFootstrikeTypeLeft));
        $activity->setAvgFootstrikeTypeRight($this->getRoundedValue($activityData->AvgFootstrikeTypeRight));
        $activity->setAvgPronationExcursionLeft($this->getRoundedValue($activityData->AvgPronationExcursionLeft, 1));
        $activity->setAvgPronationExcursionRight($this->getRoundedValue($activityData->AvgPronationExcursionRight, 1));
        $activity->setTotalStrokes($activityData->TotalStrokes);
        $activity->setTrimp($activityData->Trimp);
        $activity->setRpe($activityData->RPE);
    }

    /**
     * @param mixed $value
     * @param int $precision
     * @return float|int|null
     */
    protected function getRoundedValue($value, $precision = 0)
    {
        if (null === $value) {
            return null;
        }

        if (0 == $precision) {
            return (int)round($value);
        }

        return round($value, $precision);
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
        $subConverter = new WeatherDataToActivityConverter();
        $subConverter->setActivityWeatherDataFor($activity, $weatherData);
    }

    protected function setTrackdataFor(Training $activity, ActivityDataContainer $container)
    {
        $trackData = $this->getTrackdataFor($container);

        if (null !== $trackData) {
            $activity->setTrackdata($trackData);

            if ($trackData->hasPower()) {
                $activity->setPowerCalculated(false);
            }
        }
    }

    /**
     * @param ActivityDataContainer $container
     * @return Trackdata|null
     */
    protected function getTrackdataFor(ActivityDataContainer $container)
    {
        $trackData = new Trackdata();
        $trackData->setPauses($container->Pauses);
        $trackData->setTime($container->ContinuousData->Time ?: null);
        $trackData->setDistance($this->getRoundedContinuousDistanceData($container->ContinuousData->Distance));
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
        $trackData->setImpactGsLeft($container->ContinuousData->ImpactGsLeft ?: null);
        $trackData->setImpactGsRight($container->ContinuousData->ImpactGsRight ?: null);
        $trackData->setBrakingGsLeft($container->ContinuousData->BrakingGsLeft ?: null);
        $trackData->setBrakingGsRight($container->ContinuousData->BrakingGsRight ?: null);
        $trackData->setFootstrikeTypeLeft($container->ContinuousData->FootstrikeTypeLeft ?: null);
        $trackData->setFootstrikeTypeRight($container->ContinuousData->FootstrikeTypeRight ?: null);
        $trackData->setPronationExcursionLeft($container->ContinuousData->PronationExcursionLeft ?: null);
        $trackData->setPronationExcursionRight($container->ContinuousData->PronationExcursionRight ?: null);

        if ($trackData->isEmpty()) {
            return null;
        }

        return $trackData;
    }

    /**
     * @param array $distance [km]
     * @return array|null
     */
    protected function getRoundedContinuousDistanceData(array $distance)
    {
        if (empty($distance)) {
            return null;
        }

        return array_map(function ($v) {
            return round($v, self::DISTANCE_PRECISION_FOR_TRACK_DATA);
        }, $distance);
    }

    protected function setSwimdataFor(Training $activity, ActivityDataContainer $container)
    {
        $swimData = $this->getSwimdataFor($container);

        if (null !== $swimData) {
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
        $hrv->setData($container->RRIntervals);

        return $hrv;
    }

    protected function setRaceResultFor(Training $activity, ActivityDataContainer $container)
    {
        $raceResult = $this->getRaceResultFor($container);

        if (null !== $raceResult) {
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
