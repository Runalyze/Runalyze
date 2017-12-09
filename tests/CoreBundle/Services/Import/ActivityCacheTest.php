<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Import;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Hrv;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Swimdata;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Services\Import\ActivityCache;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ActivityCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityCache */
    protected $Cache;

    public function setUp()
    {
        $this->Cache = new ActivityCache(
            new ArrayAdapter()
        );
    }

    public function testEmptyCache()
    {
        $this->assertNull($this->Cache->get('foobar'));
    }

    public function testCachingSimpleActivity()
    {
        $activity = new Training();
        $activity->setDistance(10.0);
        $activity->setS(3476);

        $hash = $this->Cache->save($activity);

        $this->assertEquals($activity, $this->Cache->get($hash));
    }

    public function testSimpleMerge()
    {
        $activityToCache = new Training();
        $activityToCache->setDistance(10.0);
        $activityToCache->setAccount((new Account())->setName('tester'));
        $activityToCache->setRoute((new Route())->setDistance(10.0));
        $activityToCache->setTrackdata((new Trackdata())->setDistance([0.0, 5.0, 10.0]));
        $activityToCache->setSwimdata((new Swimdata())->setPoolLength(5000));
        $activityToCache->setHrv((new Hrv())->setData([820, 800, 850]));
        $activityToCache->setRaceresult((new Raceresult())->setName('Foobar event'));

        $mergerAccount = (new Account())->setName('merger');
        $activityToMerge = new Training();
        $activityToMerge->setAccount($mergerAccount);
        $activityToMerge->setDistance(12.3);

        $result = $this->Cache->get($this->Cache->save($activityToCache), $activityToMerge);

        $this->assertNotNull($result);
        $this->assertEquals(12.3, $result->getDistance());
        $this->assertEquals($mergerAccount, $result->getAccount());
        $this->assertEquals($mergerAccount, $result->getRoute()->getAccount());
        $this->assertEquals($mergerAccount, $result->getTrackdata()->getAccount());
        $this->assertEquals($mergerAccount, $result->getSwimdata()->getAccount());
        $this->assertEquals($mergerAccount, $result->getHrv()->getAccount());
        $this->assertEquals($mergerAccount, $result->getRaceresult()->getAccount());

        $this->assertEquals(10.0, $result->getRoute()->getDistance());
        $this->assertEquals([0.0, 5.0, 10.0], $result->getTrackdata()->getDistance());
        $this->assertEquals(5000, $result->getSwimdata()->getPoolLength());
        $this->assertEquals([820, 800, 850], $result->getHrv()->getData());
        $this->assertEquals('Foobar event', $result->getRaceresult()->getName());
    }
}
