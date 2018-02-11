<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Recalculation;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;
use Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationUpdater;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationManager;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RecalculationManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  RecalculationManager */
    protected $Manager;

    /** @var  Account */
    protected $Account;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var ConfigurationUpdater */
    protected $ConfigurationUpdater;

    public function setUp()
    {
        $this->Account = new Account();
        $confRepository = $this->getConfRepositoryMock();
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new PreAuthenticatedToken($this->Account, 'foo', 'bar'));

        $this->ConfigurationManager = new ConfigurationManager($confRepository, $tokenStorage);
        $list = $this->ConfigurationManager->getList();
        $list->set('data.START_TIME', '0');
        $list->set('data.VO2MAX_FORM', '0');
        $list->set('data.VO2MAX_CORRECTOR', '1.00');
        $list->set('data.BASIC_ENDURANCE', '0');

        $this->ConfigurationUpdater = new ConfigurationUpdater($confRepository, $this->ConfigurationManager);

        $this->Manager = new RecalculationManager(
            $this->ConfigurationManager,
            $this->ConfigurationUpdater,
            $this->getTrainingRepositoryMock(),
            $this->getRaceResultRepositoryMock()
        );
    }

    public function testNoScheduledTasks()
    {
        $this->assertEquals(0, $this->Manager->getNumberOfScheduledTasksFor($this->Account));

        $this->Manager->runScheduledTasks();

        $this->assertEquals('0', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
        $this->assertEquals('0', $this->ConfigurationManager->getList($this->Account)->get('data.VO2MAX_FORM'));
        $this->assertEquals('1.00', $this->ConfigurationManager->getList($this->Account)->get('data.VO2MAX_CORRECTOR'));
        $this->assertEquals('0', $this->ConfigurationManager->getList($this->Account)->get('data.BASIC_ENDURANCE'));
    }

    public function testThatMarathonShapeHasToBeCalculatedIfVO2maxShapeIsCalculated()
    {
        $this->Manager->scheduleEffectiveVO2maxShapeCalculation($this->Account);

        $this->assertEquals(2, $this->Manager->getNumberOfScheduledTasksFor($this->Account));
    }

    public function testThatTasksAreNotScheduledMultipleTimes()
    {
        $this->Manager->scheduleStartTimeCalculation($this->Account);
        $this->Manager->scheduleStartTimeCalculation($this->Account);
        $this->Manager->scheduleMarathonShapeCalculation($this->Account);
        $this->Manager->scheduleMarathonShapeCalculation($this->Account);
        $this->Manager->scheduleMarathonShapeCalculation($this->Account);

        $this->assertEquals(2, $this->Manager->getNumberOfScheduledTasksFor($this->Account));
    }

    public function testThatResultsOfTasksAreForwardedToConfiguration()
    {
        $this->Manager->scheduleEffectiveVO2maxShapeCalculation($this->Account);
        $this->Manager->scheduleMarathonShapeCalculation($this->Account);
        $this->Manager->scheduleStartTimeCalculation($this->Account);
        $this->Manager->scheduleEffectiveVO2maxCorrectionFactorCalculation($this->Account);

        $this->assertEquals(4, $this->Manager->getNumberOfScheduledTasksFor($this->Account));

        $this->Manager->runScheduledTasks();

        $this->assertEquals('123456789', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
        $this->assertEquals('35.7', $this->ConfigurationManager->getList($this->Account)->get('data.VO2MAX_FORM'));
        $this->assertEquals('0.85', $this->ConfigurationManager->getList($this->Account)->get('data.VO2MAX_CORRECTOR'));
        $this->assertEquals('68', $this->ConfigurationManager->getList($this->Account)->get('data.BASIC_ENDURANCE'));
    }

    public function testThatStartTimeCanBeUpdatedWithoutFullRecalculation()
    {
        $this->Manager->addStartTimeCheck($this->Account, 100000000, false);
        $this->Manager->runScheduledTasks();

        $this->assertEquals('100000000', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
    }

    public function testThatNewStartTimeAboveCurrentValueDoesNotChangeAnything()
    {
        $this->ConfigurationUpdater->updateStartTime($this->Account, 100000000);

        $this->Manager->addStartTimeCheck($this->Account, 123456789, false);
        $this->Manager->addStartTimeCheck($this->Account, 154321000, false);
        $this->Manager->runScheduledTasks();

        $this->assertEquals('100000000', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
    }

    public function testMultipleStartTimeUpdates()
    {
        $this->ConfigurationUpdater->updateStartTime($this->Account, 100000000);

        $this->Manager->addStartTimeCheck($this->Account, 123456789, false);
        $this->Manager->addStartTimeCheck($this->Account, 154321000, false);
        $this->Manager->addStartTimeCheck($this->Account, 98765432, false);
        $this->Manager->addStartTimeCheck($this->Account, 23456789, false);
        $this->Manager->runScheduledTasks();

        $this->assertEquals('23456789', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
    }

    public function testThatStartTimeIsNotChangedIfRemovedActivityIsTooNew()
    {
        $this->ConfigurationUpdater->updateStartTime($this->Account, 100000000);

        $this->Manager->addStartTimeCheck($this->Account, 100000001, true);
        $this->Manager->runScheduledTasks();

        $this->assertEquals('100000000', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
    }

    public function testThatStartTimeIsChangedIfRemovedActivityWasTheOldestOne()
    {
        $this->ConfigurationUpdater->updateStartTime($this->Account, 100000000);

        $this->Manager->addStartTimeCheck($this->Account, 100000000, true);
        $this->Manager->runScheduledTasks();

        $this->assertEquals('123456789', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
    }

    protected function getAccountMock()
    {
        /** @var Account */
        $account = $this
            ->getMockBuilder(Account::class)
            ->setMethods(['getId'])
            ->getMock();

        $account
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $account;
    }

    /**
     * @return TrainingRepository
     */
    protected function getTrainingRepositoryMock()
    {
        /** @var TrainingRepository */
        $repository = $this
            ->getMockBuilder(TrainingRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStartTime', 'calculateVO2maxShape', 'calculateMarathonShape'])
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('getStartTime')
            ->will($this->returnValue(123456789));

        $repository
            ->expects($this->any())
            ->method('calculateVO2maxShape')
            ->will($this->returnValue(42.0));

        $repository
            ->expects($this->any())
            ->method('calculateMarathonShape')
            ->will($this->returnValue(68));

        return $repository;
    }

    /**
     * @return RaceresultRepository
     */
    protected function getRaceResultRepositoryMock()
    {
        /** @var RaceresultRepository */
        $repository = $this
            ->getMockBuilder(RaceresultRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEffectiveVO2maxCorrectionFactor'])
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('getEffectiveVO2maxCorrectionFactor')
            ->will($this->returnValue(0.85));

        return $repository;
    }

    /**
     * @return ConfRepository
     */
    protected function getConfRepositoryMock()
    {
        /** @var ConfRepository */
        $repository = $this
            ->getMockBuilder(ConfRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findByAccount', 'findByAccountAndKey', 'save'])
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('findByAccount')
            ->will($this->returnValue([]));

        $repository
            ->expects($this->any())
            ->method('findByAccountAndKey')
            ->will($this->returnValue(null));

        $repository
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(null));

        return $repository;
    }
}
