<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\Task\VO2maxCorrectionFactorCalculation;

/**
 * @group requiresDoctrine
 */
class RaceresultRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var RaceresultRepository */
    protected $RaceresultRepository;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var Account */
    protected $Account;

    protected function setUp()
    {
        parent::setUp();

        $this->RaceresultRepository = $this->EntityManager->getRepository('CoreBundle:Raceresult');
        $this->TrainingRepository = $this->EntityManager->getRepository('CoreBundle:Training');
        $this->Account = $this->getDefaultAccount();
    }

    public function testEmptyDatabase()
    {
        $this->assertNull($this->RaceresultRepository->findByActivity(1));
        $this->assertNull($this->RaceresultRepository->findForAccount(1, 1));

        $this->assertEquals(1.0, $this->RaceresultRepository->getEffectiveVO2maxCorrectionFactor(
            $this->Account,
            $this->getDefaultAccountsRunningSport()->getId()
        ));
    }

    public function testSingleRace()
    {
        $raceActivity = $this->getActivityForDefaultAccount(mktime(3, 14, 15, 9, 26, 2016), 2400, 10.0);
        $race = $this->insertRace('Awesome pirace', $raceActivity);

        $this->assertNull($this->RaceresultRepository->findForAccount($race->getActivity()->getId(), $this->Account->getId() + 1));
        $this->assertEquals(
            $this->RaceresultRepository->findByActivity($race->getActivity()->getId()),
            $this->RaceresultRepository->findForAccount($race->getActivity()->getId(), $this->Account->getId())
        );
        $this->assertEquals(2400, $this->RaceresultRepository->findForAccount($race->getActivity()->getId(), $this->Account->getId())->getOfficialTime());

        $this->assertEmpty($this->RaceresultRepository->findBySportAndYear($this->Account, $this->getDefaultAccountsRunningSport(), 2017));
        $this->assertEmpty($this->RaceresultRepository->findBySportAndYear($this->Account, $this->getDefaultAccountsCyclingSport(), 2016));
        $this->assertEmpty($this->RaceresultRepository->findBySportAndYear(new Account(), $this->getDefaultAccountsRunningSport(), 2016));

        $races = $this->RaceresultRepository->findBySportAndYear($this->Account, $this->getDefaultAccountsRunningSport(), 2016);

        $this->assertEquals(1, count($races));
        $this->assertEquals(mktime(3, 14, 15, 9, 26, 2016), $races[0]['time']);
        $this->assertEquals('10', $races[0][0]['officialDistance']);
        $this->assertEquals('Awesome pirace', $races[0][0]['name']);

        $this->assertEquals(0, $this->getContainer()->get('app.recalculation_manager')->getNumberOfScheduledTasksFor($this->Account));
    }

    public function testFindingVO2maxCorrectionFactorForSingleRace()
    {
        $activity = $this->getActivityForDefaultAccount(time(), 2400, 10.0);
        $activity->setVO2max(50.0)->setVO2maxByTime(40.0)->setUseVO2max(true);
        $this->insertRace('foobar', $activity);

        $this->assertEquals(0.80, $this->RaceresultRepository->getEffectiveVO2maxCorrectionFactor(
            $this->Account,
            $this->getDefaultAccountsRunningSport()->getId()
        ));

        $this->assertGreaterThan(0, $this->getContainer()->get('app.recalculation_manager')->getNumberOfScheduledTasksFor($this->Account));
        $this->assertTrue($this->getContainer()->get('app.recalculation_manager')->isTaskScheduled($this->Account, VO2maxCorrectionFactorCalculation::class));
    }

    public function testFindingVO2maxCorrectionFactorForMultipleRaces()
    {
        $activity = $this->getActivityForDefaultAccount(time(), 2400, 10.0);
        $activity->setVO2max(50.0)->setVO2maxByTime(40.0)->setUseVO2max(true);
        $this->insertRace('foobar', $activity);

        $activity = $this->getActivityForDefaultAccount(time(), 2100, 10.0);
        $activity->setVO2max(48.0)->setVO2maxByTime(42.0)->setUseVO2max(true);
        $this->insertRace('foobar', $activity);

        $activity = $this->getActivityForDefaultAccount(time(), 1200, 5.0);
        $activity->setVO2max(48.0)->setVO2maxByTime(38.0)->setUseVO2max(true);
        $this->insertRace('foobar', $activity);

        $activity = $this->getActivityForDefaultAccount(time(), 2400, 5.0);
        $activity->setVO2max(10.0)->setVO2maxByTime(20.0)->setUseVO2max(true);
        $this->insertRace('foobar', $activity);

        $this->assertEquals(0.875, $this->RaceresultRepository->getEffectiveVO2maxCorrectionFactor(
            $this->Account,
            $this->getDefaultAccountsRunningSport()->getId()
        ));
    }

    /**
     * @param string $name
     * @param Training $activity
     * @return Raceresult
     */
    protected function insertRace($name, Training $activity)
    {
        $this->TrainingRepository->save($activity);

        $race = new Raceresult();
        $race->fillFromActivity($activity);
        $race->setName($name);

        $this->RaceresultRepository->save($race);

        return $race;
    }
}
