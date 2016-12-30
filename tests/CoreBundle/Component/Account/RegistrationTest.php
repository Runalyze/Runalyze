<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Component\Account\Registration;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RegistrationTest extends KernelTestCase
{
    /** @var EntityManager */
    protected $EntityManager;

    /** @var AccountRepository */
    protected $AccountRepository;

    protected function setUp()
    {
        static::bootKernel();

        $this->EntityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->AccountRepository = $this->EntityManager->getRepository('CoreBundle:Account');

        $this->clearAllOtherTablesAsLongAsCascadeDoesNotWork();
        $this->deleteAllAccounts();
    }

    protected function tearDown()
    {
        $this->clearAllOtherTablesAsLongAsCascadeDoesNotWork();
        $this->deleteAllAccounts();

        parent::tearDown();

        $this->EntityManager->close();
        $this->EntityManager = null;
    }

    protected function clearAllOtherTablesAsLongAsCascadeDoesNotWork()
    {
        /** @var EntityRepository[] $repositories */
        $repositories = [
            $this->EntityManager->getRepository('CoreBundle:Conf'),
            $this->EntityManager->getRepository('CoreBundle:Equipment'),
            $this->EntityManager->getRepository('CoreBundle:EquipmentType'),
            $this->EntityManager->getRepository('CoreBundle:Plugin'),
            $this->EntityManager->getRepository('CoreBundle:Sport'),
            $this->EntityManager->getRepository('CoreBundle:Type')
        ];

        foreach ($repositories as $repository) {
            foreach ($repository->findAll() as $item) {
                $this->EntityManager->remove($item);
            }
        }

        $this->EntityManager->flush();
    }

    protected function deleteAllAccounts()
    {
        foreach ($this->AccountRepository->findAll() as $account) {
            $this->EntityManager->remove($account);
        }

        $this->EntityManager->flush();
    }

    public function testThatRegistrationProcessWorks()
    {
        $account = new Account();
        $account->setUsername('foobar');
        $account->setMail('foo@bar.com');

        $registration = new Registration($this->EntityManager, $account);
        $registration->requireAccountActivation();
        $registration->setPassword('Pa$$w0rd', static::$kernel->getContainer()->get('security.encoder_factory'));
        $registeredAccount = $registration->registerAccount();

        $this->assertEquals('foobar', $this->AccountRepository->find($registeredAccount->getId())->getUsername());
        $this->assertNotNull($registeredAccount->getActivationHash());
    }
}
