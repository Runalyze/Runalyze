<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\User;
use Runalyze\Bundle\CoreBundle\Entity\UserRepository;

class UserRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var UserRepository */
    protected $UserRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->UserRepository = $this->EntityManager->getRepository('CoreBundle:User');
    }

    public function testEmptyDatabase()
    {
        $this->assertNull($this->UserRepository->getCurrentRestingHeartRate(new Account()));
        $this->assertNull($this->UserRepository->getCurrentMaximalHeartRate(new Account()));
    }

    public function testCurrentHeartRateStats()
    {
        $account = $this->getDefaultAccount();

        $user = new User();
        $user->setCurrentTimestamp();
        $user->setPulseRest(48);
        $user->setPulseMax(197);
        $user->setAccount($account);

        $this->UserRepository->save($user, $account);

        $this->assertEquals(48, $this->UserRepository->getCurrentRestingHeartRate($account));
        $this->assertEquals(197, $this->UserRepository->getCurrentMaximalHeartRate($account));

        $this->assertEquals('48', $this->EntityManager->getRepository('CoreBundle:Conf')->findByAccountAndKey($account, 'HF_REST')->getValue());
        $this->assertEquals('197', $this->EntityManager->getRepository('CoreBundle:Conf')->findByAccountAndKey($account, 'HF_MAX')->getValue());
    }
}
