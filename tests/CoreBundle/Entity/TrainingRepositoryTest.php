<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\BasicEndurance;
use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\VO2max;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Common\IdentifiableEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\Hrv;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Swimdata;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

/**
 * @group requiresDoctrine
 */
class TrainingRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var Account */
    protected $Account;

    protected function setUp()
    {
        parent::setUp();

        $this->TrainingRepository = $this->EntityManager->getRepository('CoreBundle:Training');
        $this->Account = $this->getDefaultAccount();
    }

    public function testEmptyDatabase()
    {
        $this->assertFalse($this->TrainingRepository->accountHasLockedTrainings(new Account()));
        $this->assertEquals(0, $this->TrainingRepository->getNumberOfActivitiesFor(new Account()));
    }

    /**
     * @param int|null $timestamp
     * @param int|float $duration
     * @param float|int|null $distance
     * @param Sport|null $sport
     * @return Training
     */
    protected function insertActivityForDefaultAccount(
        $timestamp = null,
        $duration = 3600,
        $distance = null,
        Sport $sport = null
    )
    {
        $activity = $this->getActivityForDefaultAccount($timestamp, $duration, $distance, $sport);

        $this->TrainingRepository->save($activity);

        return $activity;
    }

    public function testThatSportIsSetToDefaultIfEmpty()
    {
        $activity = new Training();
        $activity->setS(3600);
        $activity->setTime(time());
        $activity->setAccount($this->getDefaultAccount());

        $this->TrainingRepository->save($activity);

        $this->assertEquals($this->getDefaultAccountsRunningSport()->getId(), $activity->getSport()->getId());
    }

    public function testThatTypeIsRemovedIfInvalidForSport()
    {
        $type = new Type();
        $type->setName('Easy ride');
        $type->setAccount($this->getDefaultAccount());
        $type->setSport($this->getDefaultAccountsCyclingSport());

        $activity = $this->getActivityForDefaultAccount(null, 3600, 25.0);
        $activity->setType($type);

        $this->TrainingRepository->save($activity);

        $this->assertNull($activity->getType());
    }

    public function testPossibleDuplicate()
    {
        $existingActivity = $this->getActivityForDefaultAccount();
        $existingActivity->setActivityId(123456789);

        $this->TrainingRepository->save($existingActivity);

        $activityToCheck = new Training();
        $activityToCheck->setActivityId(123456789);

        $this->assertFalse($this->TrainingRepository->isPossibleDuplicate($activityToCheck));

        $activityToCheck->setAccount($this->getEmptyAccount());

        $this->assertFalse($this->TrainingRepository->isPossibleDuplicate($activityToCheck));

        $activityToCheck->setAccount($this->getDefaultAccount());

        $this->assertTrue($this->TrainingRepository->isPossibleDuplicate($activityToCheck));

        $activityToCheck->setActivityId(100000000);

        $this->assertFalse($this->TrainingRepository->isPossibleDuplicate($activityToCheck));

        $activityToCheck->setActivityId(null);

        $this->assertFalse($this->TrainingRepository->isPossibleDuplicate($activityToCheck));
    }

    public function testSpeedUnit()
    {
        $this->assertEquals(
            $this->getDefaultAccountsRunningSport()->getSpeed(),
            $this->TrainingRepository->getSpeedUnitFor(
                $this->insertActivityForDefaultAccount(null, 3600, null, $this->getDefaultAccountsRunningSport())->getId(),
                $this->Account->getId()
            )
        );

        $this->assertEquals(
            $this->getDefaultAccountsCyclingSport()->getSpeed(),
            $this->TrainingRepository->getSpeedUnitFor(
                $this->insertActivityForDefaultAccount(null, 3600, null, $this->getDefaultAccountsCyclingSport())->getId(),
                $this->Account->getId()
            )
        );
    }

    public function testNumberOfActivities()
    {
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2015));
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2016));
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 30, 2016));
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 7, 1, 2016), 3600, null, $this->getDefaultAccountsCyclingSport());

        $this->assertEquals(4, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account));
        $this->assertEquals(1, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account, 2015));
        $this->assertEquals(1, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account, 2015, $this->getDefaultAccountsRunningSport()));
        $this->assertEquals(0, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account, 2015, $this->getDefaultAccountsCyclingSport()));
        $this->assertEquals(3, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account, 2016));
        $this->assertEquals(2, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account, 2016, $this->getDefaultAccountsRunningSport()));
        $this->assertEquals(1, $this->TrainingRepository->getNumberOfActivitiesFor($this->Account, 2016, $this->getDefaultAccountsCyclingSport()));
    }

    public function testAccountStatisticsWithoutData()
    {
        $statistics = $this->TrainingRepository->getAccountStatistics($this->Account);

        $this->assertEquals(0, $statistics->getNumberOfActivities());
        $this->assertEquals(0.0, $statistics->getTotalDuration());
        $this->assertEquals(0.0, $statistics->getTotalDistance());
    }

    public function testAccountStatisticsWithData()
    {
        $this->insertActivityForDefaultAccount(null, 3600, 10.0);
        $this->insertActivityForDefaultAccount(null, 3600, 12.0);
        $this->insertActivityForDefaultAccount(null, 7200, 63.5, $this->getDefaultAccountsCyclingSport());

        $statistics = $this->TrainingRepository->getAccountStatistics($this->Account);

        $this->assertEquals(3, $statistics->getNumberOfActivities());
        $this->assertEquals(14400.0, $statistics->getTotalDuration());
        $this->assertEquals(85.5, $statistics->getTotalDistance());
    }

    public function testPosterStats()
    {
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2015), 5400, 17.5);
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2016), 3600, 12.5);
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 30, 2016), 3600, 10.0);
        $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 7, 1, 2016), 3600, 33.3, $this->getDefaultAccountsCyclingSport());

        $this->assertEquals([
            'num' => '1', 'total_distance' => '17.5', 'min_distance' => '17.5', 'max_distance' => '17.5'
        ], $this->TrainingRepository->getStatsForPoster($this->Account, $this->getDefaultAccountsRunningSport(), 2015)->getScalarResult()[0]);

        $this->assertEquals([
            'num' => '2', 'total_distance' => '22.5', 'min_distance' => '10.0', 'max_distance' => '12.5'
        ], $this->TrainingRepository->getStatsForPoster($this->Account, $this->getDefaultAccountsRunningSport(), 2016)->getScalarResult()[0]);

        $this->assertEquals([
            'num' => '0', 'total_distance' => null, 'min_distance' => null, 'max_distance' => null
        ], $this->TrainingRepository->getStatsForPoster($this->Account, $this->getDefaultAccountsCyclingSport(), 2015)->getScalarResult()[0]);

        $this->assertEquals([
            'num' => '1', 'total_distance' => '33.3', 'min_distance' => '33.3', 'max_distance' => '33.3'
        ], $this->TrainingRepository->getStatsForPoster($this->Account, $this->getDefaultAccountsCyclingSport(), 2016)->getScalarResult()[0]);
    }

    public function testLockedActivities()
    {
        $this->insertActivityForDefaultAccount();

        $this->assertFalse($this->TrainingRepository->accountHasLockedTrainings($this->Account));

        $activity = $this->getActivityForDefaultAccount(time());
        $activity->setLock(true);

        $this->TrainingRepository->save($activity);

        $this->assertTrue($this->TrainingRepository->accountHasLockedTrainings($this->Account));
    }

    public function testThatCreatedAndEditedTimestampsAreUpdatedAutomatically()
    {
        $activity = $this->insertActivityForDefaultAccount();

        $this->TrainingRepository->save($activity);

        $this->assertEquals(time(), $activity->getCreated(), '', 1);
        $this->assertNull($activity->getEdited());

        $createdAt = mktime(12, 0, 0, 3, 14, 2017);
        $activity->setCreated($createdAt);

        $this->TrainingRepository->save($activity);

        $this->assertEquals($createdAt, $activity->getCreated());
        $this->assertEquals(time(), $activity->getEdited(), '', 1);
    }

    public function testThatActivityCanExistWithoutRelatedObjects()
    {
        $activity = $this->insertActivityForDefaultAccount();

        $this->TrainingRepository->save($activity);

        /** @var Training $insertedActivity */
        $insertedActivity = $this->TrainingRepository->find($activity->getId());

        $this->assertNull($insertedActivity->getTrackdata());
        $this->assertNull($insertedActivity->getSwimdata());
        $this->assertNull($insertedActivity->getHrv());
        $this->assertNull($insertedActivity->getRaceresult());

        $this->assertInstanceOf(RoundCollection::class, $insertedActivity->getSplits());
        $this->assertTrue($insertedActivity->getSplits()->isEmpty());
    }

    public function testActivityWithRelatedObjects()
    {
        $activity = $this->getActivityForDefaultAccount(
            123456789,
            116,
            0.5
        );

        $route = new Route();
        $route->setElevationsCorrected([122, 125, 128, 130, 129, 130]);

        $trackData = new Trackdata();
        $trackData->setDistance([0.0, 0.1, 0.2, 0.3, 0.4, 0.5]);
        $trackData->setHeartrate([140, 141, 140, 142, 143, 143]);

        $hrv = new Hrv();
        $hrv->setData([428, 429, 425, 426, 428, 440, 424, 415, 422, 465, 421, 420]);

        $activity->setRoute($route);
        $activity->setTrackdata($trackData);
        $activity->setHrv($hrv);

        $this->TrainingRepository->save($activity);

        $result = $this->TrainingRepository->findForAccount($activity->getId(), $this->getDefaultAccount()->getId());

        $this->assertTrue($result->hasRoute());
        $this->assertTrue($result->hasTrackdata());
        $this->assertTrue($result->hasHrv());

        $this->assertGreaterThan(0, $result->getRoute()->getElevation());
        $this->assertNotNull($result->getClimbScore());
    }

    public function testActivityWithRaceResult()
    {
        $activity = $this->getActivityForDefaultAccount(
            123456789,
            629,
            3.0
        );

        $raceResult = new Raceresult();
        $raceResult->fillFromActivity($activity);
        $raceResult->setName('Event');
        $activity->setRaceresult($raceResult);

        $this->TrainingRepository->save($activity);

        $result = $this->TrainingRepository->findForAccount($activity->getId(), $this->getDefaultAccount()->getId());

        $this->assertTrue($result->hasRaceresult());
        $this->assertEquals(3.0, $result->getRaceresult()->getOfficialDistance());
    }

    public function testActivityWithSwimData()
    {
        $swimData = new Swimdata();
        $swimData->setPoolLength(5000);
        $swimData->setStroke([32, 30, 35, 28]);

        $activity = $this->getActivityForDefaultAccount(
            123456789,
            300,
            0.2
        );
        $activity->setSwimdata($swimData);

        $this->TrainingRepository->save($activity);

        $result = $this->TrainingRepository->findForAccount($activity->getId(), $this->getDefaultAccount()->getId());

        $this->assertTrue($result->hasSwimdata());
        $this->assertEquals(125, $result->getTotalStrokes());
        $this->assertEquals(5000, $result->getSwimdata()->getPoolLength());
    }

    public function testStartTimeForEmptyAccount()
    {
        $this->assertNull($this->TrainingRepository->getStartTime($this->getDefaultAccount()));
    }

    public function testStartTimeForSimpleExample()
    {
        $this->insertActivityForDefaultAccount(987654321);
        $this->insertActivityForDefaultAccount(123456789);

        $this->assertEquals(123456789, $this->TrainingRepository->getStartTime($this->getDefaultAccount()));
    }

    public function testVO2maxShapeCalculationForEmptyAccount()
    {
        $this->assertEquals(0.0, $this->TrainingRepository->calculateVO2maxShape(
            $this->getDefaultAccount(),
            new VO2max(),
            $this->getDefaultAccountsRunningSport()->getId(),
            time()
        ));
    }

    public function testVO2maxShapeCalculationForASingleActivity()
    {
        $activity = $this->getActivityForDefaultAccount(time() - 86400, 3600, 10.0)->setPulseAvg(160);

        $this->TrainingRepository->save($activity);

        $this->assertEquals($activity->getVO2max(), $this->TrainingRepository->calculateVO2maxShape(
            $this->getDefaultAccount(),
            new VO2max(),
            $this->getDefaultAccountsRunningSport()->getId(),
            time()
        ), '', 0.001);
    }

    public function testVO2maxShapeCalculationForSomeActivities()
    {
        $config = new VO2max();
        $config->set('VO2MAX_USE_CORRECTION_FOR_ELEVATION', 'true');

        $activity1 = $this->getActivityForDefaultAccount(time() - 86400, 1000, 4.0)->setPulseAvg(160);
        $activity2 = $this->getActivityForDefaultAccount(time() - 2 * 86400, 2000, 8.0)->setPulseAvg(160);
        $activity3 = $this->getActivityForDefaultAccount(time() - 200 * 86400, 10000, 40.0)->setPulseAvg(160);

        $this->TrainingRepository->save($activity1);
        $this->TrainingRepository->save($activity2);
        $this->TrainingRepository->save($activity3);

        $expectedShape = ($activity1->getVO2max() + 2 * $activity2->getVO2max()) / 3;

        $this->assertEquals($expectedShape, $this->TrainingRepository->calculateVO2maxShape(
            $this->getDefaultAccount(),
            $config,
            $this->getDefaultAccountsRunningSport()->getId(),
            time()
        ), '', 0.001);
    }

    public function testMarathonShapeCalculationForEmptyAccount()
    {
        $this->assertEquals(0.0, $this->TrainingRepository->calculateMarathonShape(
            $this->getDefaultAccount(),
            new BasicEndurance(),
            50.0,
            $this->getDefaultAccountsRunningSport()->getId(),
            time()
        ));
    }

    public function testMarathonShapeCalculationForOnlyLongJog()
    {
        $date = mktime(12, 0, 0, 1, 10, 2015);
        $config = new BasicEndurance();
        $config->set('BE_DAYS_FOR_LONGJOGS', '10');
        $config->set('BE_DAYS_FOR_WEEK_KM', '365');
        $config->set('BE_PERCENTAGE_WEEK_KM', '0.00');

        $this->insertActivityForDefaultAccount($date - 5 * 86400, 10800, 32.5);

        $this->assertEquals(70.0, $this->TrainingRepository->calculateMarathonShape(
            $this->getDefaultAccount(),
            $config,
            60.0,
            $this->getDefaultAccountsRunningSport()->getId(),
            $date
        ));
    }

    public function testActivityNavigation()
    {
        $activity1 = $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2015));
        $activity2 = $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2015));
        $activity3 = $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 1, 2016));
        $activity4 = $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 30, 2016));
        $activity5 = $this->insertActivityForDefaultAccount(mktime(12, 0, 0, 6, 30, 2016));

        $this->assertNull($this->TrainingRepository->getIdOfPreviousActivity($activity1));
        $this->assertEquals($activity2->getId(), $this->TrainingRepository->getIdOfNextActivity($activity1));

        $this->assertEquals($activity1->getId(), $this->TrainingRepository->getIdOfPreviousActivity($activity2));
        $this->assertEquals($activity3->getId(), $this->TrainingRepository->getIdOfNextActivity($activity2));

        $this->assertEquals($activity2->getId(), $this->TrainingRepository->getIdOfPreviousActivity($activity3));
        $this->assertEquals($activity4->getId(), $this->TrainingRepository->getIdOfNextActivity($activity3));

        $this->assertEquals($activity3->getId(), $this->TrainingRepository->getIdOfPreviousActivity($activity4));
        $this->assertEquals($activity5->getId(), $this->TrainingRepository->getIdOfNextActivity($activity4));

        $this->assertEquals($activity4->getId(), $this->TrainingRepository->getIdOfPreviousActivity($activity5));
        $this->assertNull($this->TrainingRepository->getIdOfNextActivity($activity5));
    }

    public function testEquipmentStatistics()
    {
        $someEquipment = $this->EntityManager->getRepository('CoreBundle:Equipment')->findBy(
            ['account' => $this->getDefaultAccount()],
            null,
            3
        );

        if (count($someEquipment) < 3) {
            $this->markTestSkipped('Test requires at least three existing equipment objects for default account.');
        } else {
            $activity = $this->getActivityForDefaultAccount(null, 3600, 10.0);
            $activity->addEquipment($someEquipment[0]);

            $this->TrainingRepository->save($activity);

            $this->assertEquals(3600, $someEquipment[0]->getTime());
            $this->assertEquals(10.0, $someEquipment[0]->getDistance());
            $this->assertEquals(0, $someEquipment[1]->getTime());
            $this->assertEquals(0.0, $someEquipment[1]->getDistance());
            $this->assertEquals(0, $someEquipment[2]->getTime());
            $this->assertEquals(0.0, $someEquipment[2]->getDistance());

            $activity->addEquipment($someEquipment[1]);
            $activity->setDistance(12.0);
            $activity->setS(3580);

            $this->TrainingRepository->save($activity);

            $this->refreshEquipment($someEquipment);

            $this->assertEquals(3580, $someEquipment[0]->getTime());
            $this->assertEquals(12.0, $someEquipment[0]->getDistance());
            $this->assertEquals(3580, $someEquipment[1]->getTime());
            $this->assertEquals(12.0, $someEquipment[1]->getDistance());
            $this->assertEquals(0, $someEquipment[2]->getTime());
            $this->assertEquals(0.0, $someEquipment[2]->getDistance());

            $activity->removeEquipment($someEquipment[0]);
            $activity->addEquipment($someEquipment[2]);

            $this->TrainingRepository->save($activity);

            $this->refreshEquipment($someEquipment);

            $this->assertEquals(0, $someEquipment[0]->getTime());
            $this->assertEquals(0.0, $someEquipment[0]->getDistance());
            $this->assertEquals(3580, $someEquipment[1]->getTime());
            $this->assertEquals(12.0, $someEquipment[1]->getDistance());
            $this->assertEquals(3580, $someEquipment[2]->getTime());
            $this->assertEquals(12.0, $someEquipment[2]->getDistance());

            $this->TrainingRepository->remove($activity);

            $this->refreshEquipment($someEquipment);

            $this->assertEquals(0, $someEquipment[0]->getTime());
            $this->assertEquals(0.0, $someEquipment[0]->getDistance());
            $this->assertEquals(0, $someEquipment[1]->getTime());
            $this->assertEquals(0.0, $someEquipment[1]->getDistance());
            $this->assertEquals(0, $someEquipment[2]->getTime());
            $this->assertEquals(0.0, $someEquipment[2]->getDistance());
        }
    }

    /**
     * @param Equipment[] $entities
     */
    protected function refreshEquipment(array $entities)
    {
        foreach ($entities as $key => $entity) {
            $this->EntityManager->getUnitOfWork()->refresh($entity);
        }
    }
}
