<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\User;
use Runalyze\Bundle\CoreBundle\Entity\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    /** @var EntityManager */
    protected $EntityManager;

    /** @var UserRepository */
    protected $UserRepository;

    protected function setUp()
    {
        static::bootKernel();

        $this->EntityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->UserRepository = $this->EntityManager->getRepository('CoreBundle:User');

        $this->clearDatabase();
    }

    protected function tearDown()
    {
        $this->clearDatabase();

        parent::tearDown();

        $this->EntityManager->close();
        $this->EntityManager = null;
    }

    protected function clearDatabase()
    {
        /** @var EntityRepository[] $repositories */
        $repositories = [
            $this->EntityManager->getRepository('CoreBundle:User'),
            $this->EntityManager->getRepository('CoreBundle:Conf'),
            $this->EntityManager->getRepository('CoreBundle:Account')
        ];

        foreach ($repositories as $repository) {
            foreach ($repository->findAll() as $item) {
                $this->EntityManager->remove($item);
            }
        }

        $this->EntityManager->flush();
    }

    /**
     * @param string $username
     * @param string $mail
     * @return Account
     */
    protected function getNewAccount($username, $mail = '')
    {
        if ('' == $mail) {
            $mail = $username.'@test.com';
        }

        return (new Account())
            ->setUsername($username)
            ->setMail($mail)
            ->setPassword('');
    }

    public function testEmptyDatabase()
    {
        $this->assertNull($this->UserRepository->getCurrentRestingHeartRate(new Account()));
        $this->assertNull($this->UserRepository->getCurrentMaximalHeartRate(new Account()));
    }

    public function testCurrentHeartRateStats()
    {
        $account = $this->getNewAccount('tester');
        $this->EntityManager->getRepository('CoreBundle:Account')->save($account);

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
