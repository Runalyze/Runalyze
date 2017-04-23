<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Profile\Sport\SportProfile;

class SportRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var SportRepository */
    protected $SportRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->SportRepository = $this->EntityManager->getRepository('CoreBundle:Sport');
    }

    public function testEmptyAccount()
    {
        $account = $this->getEmptyAccount();

        $this->assertEmpty($this->SportRepository->findAllFor($account));
        $this->assertEmpty($this->SportRepository->findWithDistancesFor($account));
        $this->assertEmpty($this->SportRepository->getUsedInternalSportIdsFor($account));

        $this->assertTrue($this->SportRepository->isInternalTypeFree(SportProfile::RUNNING, $account));
        $this->assertTrue($this->SportRepository->isInternalTypeFree(SportProfile::CYCLING, $account));
        $this->assertTrue($this->SportRepository->isInternalTypeFree(SportProfile::SWIMMING, $account));

        $this->assertNull($this->SportRepository->findRunningFor($account, true));
        $this->assertNull($this->SportRepository->findRunningFor($account)->getId());
    }

    public function testDefaultAccount()
    {
        $account = $this->getDefaultAccount();

        $this->assertFalse($this->SportRepository->isInternalTypeFree(SportProfile::RUNNING, $account));
        $this->assertFalse($this->SportRepository->isInternalTypeFree(SportProfile::CYCLING, $account));
        $this->assertFalse($this->SportRepository->isInternalTypeFree(SportProfile::SWIMMING, $account));

        $this->assertEquals($this->getDefaultAccountsRunningSport()->getId(), $this->SportRepository->findRunningFor($account)->getId());
    }
}
