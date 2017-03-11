<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Tests\DataFixtures\ORM\LoadAccountData;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractRepositoryTestCase extends WebTestCase
{
    /** @var  Client */
    protected $Client;

    /** @var ReferenceRepository */
    protected $Fixtures;

    /** @var EntityManager */
    protected $EntityManager;

    protected function setUp()
    {
        $this->Fixtures = $this->loadFixtures([
            LoadAccountData::class
        ])->getReferenceRepository();

        $this->Client = $this->getContainer()->get('test.client');
        $this->Client->disableReboot();

        $this->EntityManager = $this->getContainer()->get('doctrine')->getManager();
        $this->EntityManager->clear();

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (null !== $this->EntityManager) {
            $this->EntityManager->close();
            $this->EntityManager = null;
        }
    }

    /**
     * @return Account
     */
    protected function getDefaultAccount()
    {
        return $this->Fixtures->getReference('account-default');
    }

    /**
     * @return Sport
     */
    protected function getDefaultAccountsRunningSport()
    {
        return $this->Fixtures->getReference('account-default.sport-running');
    }

    /**
     * @return Sport
     */
    protected function getDefaultAccountsCyclingSport()
    {
        return $this->Fixtures->getReference('account-default.sport-cycling');
    }

    /**
     * @return Account
     */
    protected function getEmptyAccount()
    {
        return $this->Fixtures->getReference('account-empty');
    }
}
