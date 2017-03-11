<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Account;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Runalyze\Bundle\CoreBundle\Component\Account\Registration;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Symfony\Bundle\FrameworkBundle\Client;

class RegistrationTest extends WebTestCase
{
    /** @var Client */
    protected $Client;

    /** @var EntityManager */
    protected $EntityManager;

    /** @var AccountRepository */
    protected $AccountRepository;

    protected function setUp()
    {
        $this->loadFixtures([])->getReferenceRepository();

        $this->Client = $this->getContainer()->get('test.client');
        $this->Client->disableReboot();

        $this->EntityManager = $this->getContainer()->get('doctrine')->getManager();
        $this->EntityManager->clear();

        $this->AccountRepository = $this->EntityManager->getRepository('CoreBundle:Account');

        parent::setUp();
    }

    public function testThatRegistrationProcessWorks()
    {
        $account = new Account();
        $account->setUsername('foobar');
        $account->setMail('foo@bar.com');

        $registration = new Registration($this->EntityManager, $account);
        $registration->requireAccountActivation();
        $registration->setPassword('Pa$$w0rd', $this->getContainer()->get('security.encoder_factory'));
        $registeredAccount = $registration->registerAccount();

        $this->assertEquals('foobar', $this->AccountRepository->find($registeredAccount->getId())->getUsername());
        $this->assertNotNull($registeredAccount->getActivationHash());
    }
}
