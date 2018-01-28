<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Profile\Sport\SportProfile;

/**
 * @group requiresDoctrine
 */
class SportRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var SportRepository */
    protected $SportRepository;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var Account */
    protected $Account;

    protected function setUp()
    {
        parent::setUp();

        $this->SportRepository = $this->EntityManager->getRepository('CoreBundle:Sport');
        $this->TrainingRepository = $this->EntityManager->getRepository('CoreBundle:Training');
        $this->Account = $this->getDefaultAccount();
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

    public function testEmptyAccount()
    {
        $account = $this->getEmptyAccount();

        $this->assertEmpty($this->SportRepository->findAllFor($account));
        $this->assertEmpty($this->SportRepository->findWithDistancesFor($account));
        $this->assertEmpty($this->SportRepository->getUsedInternalSportIdsFor($account));

        $this->assertTrue($this->SportRepository->isInternalTypeFree(SportProfile::RUNNING, $account));
        $this->assertTrue($this->SportRepository->isInternalTypeFree(SportProfile::CYCLING, $account));
        $this->assertTrue($this->SportRepository->isInternalTypeFree(SportProfile::SWIMMING, $account));

        $this->assertNull($this->SportRepository->findRunningFor($account, true));
        $this->assertNull($this->SportRepository->findRunningFor($account)->getId());

        $this->assertNull($this->SportRepository->findThisOrAny(1, $account));
    }

    public function testDefaultAccount()
    {
        $account = $this->getDefaultAccount();

        $this->assertFalse($this->SportRepository->isInternalTypeFree(SportProfile::RUNNING, $account));
        $this->assertFalse($this->SportRepository->isInternalTypeFree(SportProfile::CYCLING, $account));
        $this->assertFalse($this->SportRepository->isInternalTypeFree(SportProfile::SWIMMING, $account));

        $this->assertEquals($this->getDefaultAccountsRunningSport()->getId(), $this->SportRepository->findRunningFor($account)->getId());

        $this->assertEquals($this->getDefaultAccountsRunningSport(), $this->SportRepository->findThisOrAny($this->getDefaultAccountsRunningSport()->getId(), $account));
        $this->assertNotNull($this->SportRepository->findThisOrAny(-123, $account));
    }

    public function testSportStatisticsWithoutData()
    {
        $statistics = $this->SportRepository->getSportStatisticsSince(null, $this->Account);

        $this->assertEquals(0, $statistics->getCount());
        $this->assertEmpty($statistics->getStatistics());
    }

    public function testSportStatisticsWithData()
    {
        $this->insertActivityForDefaultAccount(1400000000, 3600, 10.0, $this->getDefaultAccountsRunningSport());
        $this->insertActivityForDefaultAccount(1500000000, 3400, 12.0, $this->getDefaultAccountsRunningSport());
        $this->insertActivityForDefaultAccount(1400000000, 7600, 63.5, $this->getDefaultAccountsCyclingSport());

        $allTimeStatistics = $this->SportRepository->getSportStatisticsSince(null, $this->Account);
        $allTimeStatisticsRunning = $allTimeStatistics->getStatisticFor($this->getDefaultAccountsRunningSport());
        $allTimeStatisticsCycling = $allTimeStatistics->getStatisticFor($this->getDefaultAccountsCyclingSport());

        $this->assertEquals(2, $allTimeStatistics->getCount());

        $this->assertEquals(7000.0, $allTimeStatisticsRunning->getTotalDuration());
        $this->assertEquals(22.0, $allTimeStatisticsRunning->getTotalDistance());
        $this->assertEquals(2, $allTimeStatisticsRunning->getNumberOfActivities());

        $this->assertEquals(7600.0, $allTimeStatisticsCycling->getTotalDuration());
        $this->assertEquals(63.5, $allTimeStatisticsCycling->getTotalDistance());
        $this->assertEquals(1, $allTimeStatisticsCycling->getNumberOfActivities());
    }
}
