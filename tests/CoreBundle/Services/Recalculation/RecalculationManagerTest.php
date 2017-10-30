<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Recalculation;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;
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

    public function setUp()
    {
        $this->Account = new Account();
        $confRepository = $this->getConfRepositoryMock();
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new PreAuthenticatedToken($this->Account, 'foo', 'bar'));

        $this->ConfigurationManager = new ConfigurationManager($confRepository, $tokenStorage);
        $this->ConfigurationManager->getList()->set('data.START_TIME', '0');
        $this->ConfigurationManager->getList()->set('data.VO2MAX_FORM', '0');
        $this->ConfigurationManager->getList()->set('data.BASIC_ENDURANCE', '0');

        $this->Manager = new RecalculationManager(
            $this->ConfigurationManager,
            new ConfigurationUpdater($confRepository, $this->ConfigurationManager),
            $this->getTrainingRepositoryMock()
        );
    }

    public function testNoScheduledTasks()
    {
        $this->assertEquals(0, $this->Manager->getNumberOfScheduledTasksFor($this->Account));

        $this->Manager->runScheduledTasks();

        $this->assertEquals('0', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
        $this->assertEquals('0', $this->ConfigurationManager->getList($this->Account)->get('data.VO2MAX_FORM'));
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
        $this->Manager->scheduleStartTimeCalculation($this->Account);
        $this->Manager->scheduleEffectiveVO2maxShapeCalculation($this->Account);
        $this->Manager->scheduleMarathonShapeCalculation($this->Account);

        $this->assertEquals(3, $this->Manager->getNumberOfScheduledTasksFor($this->Account));

        $this->Manager->runScheduledTasks();

        $this->assertEquals('123456789', $this->ConfigurationManager->getList($this->Account)->get('data.START_TIME'));
        $this->assertEquals('42.0', $this->ConfigurationManager->getList($this->Account)->get('data.VO2MAX_FORM'));
        $this->assertEquals('68', $this->ConfigurationManager->getList($this->Account)->get('data.BASIC_ENDURANCE'));
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
