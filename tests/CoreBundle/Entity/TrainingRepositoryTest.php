<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;

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
        $activity = $this->getActivitiyForDefaultAccount($timestamp, $duration, $distance, $sport);

        $this->TrainingRepository->save($activity);

        return $activity;
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

        $activity = $this->getActivitiyForDefaultAccount(time());
        $activity->setLock(true);

        $this->TrainingRepository->save($activity);

        $this->assertTrue($this->TrainingRepository->accountHasLockedTrainings($this->Account));
    }
}
