<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;

class FlatOrHillyAnalyzerTest extends \PHPUnit_Framework_TestCase
{
    /** @var FlatOrHillyAnalyzer */
    protected $Analyzer;

    protected function setUp()
    {
        $this->Analyzer = new FlatOrHillyAnalyzer();
    }

    public function testThatElevationsAreRequired()
    {
        $trackdata = new Trackdata();
        $trackdata->setDistance(range(0.0, 13.5, 1.0));

        $activity = new Training();
        $activity->setDistance(13.5);
        $activity->setElevation(412);
        $activity->setTrackdata($trackdata);
        $activity->setRoute(new Route());

        $this->assertNull($this->Analyzer->calculatePercentageFlatFor($activity));
    }

    public function testThatDistancesAreRequired()
    {
        $route = new Route();
        $route->setElevationsCorrected([100, 200, 350, 512, 270]);

        $activity = new Training();
        $activity->setDistance(13.5);
        $activity->setElevation(412);
        $activity->setTrackdata(new Trackdata());
        $activity->setRoute($route);

        $this->assertNull($this->Analyzer->calculatePercentageFlatFor($activity));
    }

    public function testSimpleActivity()
    {
        $trackdata = new Trackdata();
        $trackdata->setDistance([0.0, 1.0, 2.2, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]);

        $route = new Route();
        $route->setElevationsCorrected([100, 128, 178, 188, 200, 190, 165, 150, 140, 100, 100]);

        $activity = new Training();
        $activity->setTrackdata($trackdata);
        $activity->setRoute($route);

        $this->assertEquals(0.30, $this->Analyzer->calculatePercentageFlatFor($activity, 0.01));
        $this->assertEquals(0.58, $this->Analyzer->calculatePercentageFlatFor($activity, 0.02));
        $this->assertEquals(0.78, $this->Analyzer->calculatePercentageFlatFor($activity, 0.03));
    }

    public function testArrayWithShortPeak()
    {
        $this->assertEquals(0.625, $this->Analyzer->calculatePercentageFlatForArrays(
            [0.00, 0.01, 0.05, 0.10, 0.20, 1.00, 1.60],
            [100, 104, 104, 104, 104, 104, 175],
            0.05
        ));
    }
}
