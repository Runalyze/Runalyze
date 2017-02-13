<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\RouteRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RouteRepositoryTest extends KernelTestCase
{
    /** @var EntityManager */
    protected $EntityManager;

    /** @var RouteRepository */
    protected $RouteRepository;

    protected function setUp()
    {
        static::bootKernel();

        $this->EntityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->RouteRepository = $this->EntityManager->getRepository('CoreBundle:Route');

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
            $this->EntityManager->getRepository('CoreBundle:Route'),
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

    public function testCheckingForLockedRoutes()
    {
        $account = $this->getNewAccount('tester');
        $this->EntityManager->getRepository('CoreBundle:Account')->save($account);

        $this->assertFalse($this->RouteRepository->accountHasLockedRoutes($account));

        $this->RouteRepository->save((new Route())->setAccount($account));

        $this->assertFalse($this->RouteRepository->accountHasLockedRoutes($account));

        $this->RouteRepository->save((new Route())->setAccount($account)->setLock(true));

        $this->assertTrue($this->RouteRepository->accountHasLockedRoutes($account));
    }
}
