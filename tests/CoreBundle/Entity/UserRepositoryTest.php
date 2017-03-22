<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\User;
use Runalyze\Bundle\CoreBundle\Entity\UserRepository;

class UserRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var UserRepository */
    protected $UserRepository;

    /** @var Account */
    protected $Account;

    protected function setUp()
    {
        parent::setUp();

        $this->UserRepository = $this->EntityManager->getRepository('CoreBundle:User');
        $this->Account = $this->getDefaultAccount();
    }

    public function testEmptyDatabase()
    {
        $this->assertNull($this->UserRepository->getCurrentRestingHeartRate(new Account()));
        $this->assertNull($this->UserRepository->getCurrentMaximalHeartRate(new Account()));
        $this->assertNull($this->UserRepository->getLatestEntryFor(new Account()));
        $this->assertEmpty($this->UserRepository->findAllFor(new Account()));
    }

    /**
     * @param int $heartRateMax
     * @param int $heartRateRest
     * @param null|int $timestamp
     * @return User
     */
    protected function insertDataForDefaultAccount($heartRateMax, $heartRateRest, $timestamp = null)
    {
        $user = (new User())
            ->setPulseMax($heartRateMax)
            ->setPulseRest($heartRateRest)
            ->setTime($timestamp ?: time())
            ->setAccount($this->Account);

        $this->UserRepository->save($user, $this->Account);

        return $user;
    }

    public function testCurrentHeartRateStats()
    {
        $this->insertDataForDefaultAccount(197, 48);

        $this->assertEquals(197, $this->UserRepository->getCurrentMaximalHeartRate($this->Account));
        $this->assertEquals(48, $this->UserRepository->getCurrentRestingHeartRate($this->Account));

        $this->assertEquals('197', $this->EntityManager->getRepository('CoreBundle:Conf')->findByAccountAndKey($this->Account, 'HF_MAX')->getValue());
        $this->assertEquals('48', $this->EntityManager->getRepository('CoreBundle:Conf')->findByAccountAndKey($this->Account, 'HF_REST')->getValue());
    }

    public function testThatZeroesAreIgnored()
    {
        $this->insertDataForDefaultAccount(0, 0, time());
        $this->insertDataForDefaultAccount(195, 0, time() - 300);
        $this->insertDataForDefaultAccount(0, 53, time() - 600);
        $this->insertDataForDefaultAccount(200, 60, time() - 900);

        $this->assertEquals(195, $this->UserRepository->getCurrentMaximalHeartRate($this->Account));
        $this->assertEquals(53, $this->UserRepository->getCurrentRestingHeartRate($this->Account));

        $latestEntry = $this->UserRepository->getLatestEntryFor($this->Account);
        $this->assertEquals(0, $latestEntry->getPulseMax());
        $this->assertEquals(0, $latestEntry->getPulseRest());
    }
}
