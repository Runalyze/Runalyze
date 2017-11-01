<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\BasicEndurance;
use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\VO2max;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
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
}
