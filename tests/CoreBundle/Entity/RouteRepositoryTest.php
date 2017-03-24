<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\RouteRepository;

class RouteRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var RouteRepository */
    protected $RouteRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->RouteRepository = $this->EntityManager->getRepository('CoreBundle:Route');
    }

    public function testCheckingForLockedRoutes()
    {
        $account = $this->getDefaultAccount();

        $this->assertFalse($this->RouteRepository->accountHasLockedRoutes($account));

        $this->RouteRepository->save((new Route())->setAccount($account));

        $this->assertFalse($this->RouteRepository->accountHasLockedRoutes($account));

        $this->RouteRepository->save((new Route())->setAccount($account)->setLock(true));

        $this->assertTrue($this->RouteRepository->accountHasLockedRoutes($account));
    }
}
