<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\RouteRepository;

/**
 * @group requiresDoctrine
 */
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

    public function testSynchronizationTasksForOnlyNullGeohashes()
    {
        $route = new Route();
        $route->setAccount($this->getDefaultAccount());
        $route->setGeohashes(['7zzzzzzzzzzz', '7zzzzzzzzzzz', '7zzzzzzzzzzz']);
        $route->setElevationsOriginal([127, 135, 128]);
        $route->setElevationsSource('device');

        $this->RouteRepository->save($route);

        $this->assertNull($route->getGeohashes());
        $this->assertNull($route->getMin());
        $this->assertNull($route->getMax());
        $this->assertNull($route->getStartpoint());
        $this->assertNull($route->getEndpoint());
        $this->assertEquals('', $route->getElevationsSource());
    }

    public function testSynchronizationTasks()
    {
        $route = new Route();
        $route->setAccount($this->getDefaultAccount());
        $route->setGeohashes(['7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhm6zkm', 'u1xjnxhjr7wb']);
        $route->setElevationsCorrected([127, 135, 134, 134]);
        $route->setElevationsSource('device');

        $this->RouteRepository->save($route);

        $this->assertEquals(['7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhm6zkm', 'u1xjnxhjr7wb'], $route->getGeohashes());
        $this->assertEquals('u1xjnxhj49', $route->getMin());
        $this->assertEquals('u1xjnxhm6z', $route->getMax());
        $this->assertEquals('u1xjnxhj49', $route->getStartpoint());
        $this->assertEquals('u1xjnxhjr7', $route->getEndpoint());
        $this->assertEquals('device', $route->getElevationsSource());
    }
}
