<?php

namespace Runalyze\Bundle\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Tests\DataFixtures\ORM\LoadAccountData;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractFixturesAwareWebTestCase extends WebTestCase
{
    /** @var  Client */
    protected $Client;

    /** @var ReferenceRepository */
    protected $Fixtures;

    /** @var array|null */
    protected $FixtureClasses = null;

    /** @var EntityManager */
    protected $EntityManager;

    protected function setUp()
    {
        if (null === $this->FixtureClasses) {
            $this->FixtureClasses = [
                LoadAccountData::class
            ];
        }

        $this->Fixtures = $this->loadFixtures($this->FixtureClasses)->getReferenceRepository();

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
     * @return EquipmentType
     */
    protected function getDefaultAccountsClothesType()
    {
        return $this->Fixtures->getReference('account-default.equipment-type-clothes');
    }

    /**
     * @return Account
     */
    protected function getEmptyAccount()
    {
        return $this->Fixtures->getReference('account-empty');
    }

    /**
     * @param int|null $timestamp
     * @param int|float $duration
     * @param float|int|null $distance
     * @param Sport|null $sport
     * @return Training
     */
    protected function getActivitiyForDefaultAccount(
        $timestamp = null,
        $duration = 3600,
        $distance = null,
        Sport $sport = null
    )
    {
        return (new Training())
            ->setS($duration)
            ->setTime($timestamp ?: time())
            ->setDistance($distance)
            ->setSport($sport ?: $this->getDefaultAccountsRunningSport())
            ->setAccount($this->getDefaultAccount());
    }
}
