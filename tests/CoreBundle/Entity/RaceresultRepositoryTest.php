<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;

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
    }

    public function testSingleRace()
    {
        $raceActivity = $this->getActivitiyForDefaultAccount(mktime(3, 14, 15, 9, 26, 2016), 2400, 10.0);

        $this->TrainingRepository->save($raceActivity);

        $race = new Raceresult();
        $race->fillFromActivity($raceActivity);
        $race->setName('Awesome pirace');

        $this->RaceresultRepository->save($race);

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
    }
}
