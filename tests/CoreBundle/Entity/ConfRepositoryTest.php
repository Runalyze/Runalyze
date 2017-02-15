<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfRepositoryTest extends KernelTestCase
{
    /** @var EntityManager */
    protected $EntityManager;

    /** @var ConfRepository */
    protected $ConfRepository;

    protected function setUp()
    {
        static::bootKernel();

        $this->EntityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->ConfRepository = $this->EntityManager->getRepository('CoreBundle:Conf');

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
        $account = $this->getNewAccount('tester');
        $this->EntityManager->getRepository('CoreBundle:Account')->save($account);

        $this->assertEquals([], $this->ConfRepository->findByAccount($account));
        $this->assertNull($this->ConfRepository->findByAccountAndKey($account, 'SOME_RANDOM_KEY'));
    }

    public function testInsertingAndUpdatingKey()
    {
        $account = $this->getNewAccount('tester');
        $this->EntityManager->getRepository('CoreBundle:Account')->save($account);

        $conf = $this->ConfRepository->updateOrInsert($account, 'cat', 'SOME_KEY', 'foo');

        $this->assertEquals([$conf], $this->ConfRepository->findByAccount($account));
        $this->assertEquals($conf, $this->ConfRepository->findByAccountAndKey($account, 'SOME_KEY'));
        $this->assertEquals('foo', $conf->getValue());

        $this->ConfRepository->updateOrInsert($account, 'cat', 'SOME_KEY', 'bar');

        $this->assertEquals(1, count($this->ConfRepository->findByAccount($account)));
        $this->assertEquals('bar', $this->ConfRepository->findByAccountAndKey($account, 'SOME_KEY')->getValue());
    }
}
