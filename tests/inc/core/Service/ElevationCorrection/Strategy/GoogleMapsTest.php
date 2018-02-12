<?php

namespace Runalyze\Tests\Service\ElevationCorrection\Strategy;

use GuzzleHttp\Psr7\Response;
use Runalyze\Service\ElevationCorrection\Strategy\GoogleMaps;
use Runalyze\Tests\Service\HttpClientAwareTestCaseTrait;

class GoogleMapsTest extends \PHPUnit_Framework_TestCase
{
    use HttpClientAwareTestCaseTrait;

    public function testEmptyResponse()
    {
        $google = new GoogleMaps($this->getMockForResponses([
            new Response(200, [], '')
        ]));

        $this->assertNull($google->loadAltitudeData([49.444], [7.769]));
    }

    /**
     * @see http://maps.googleapis.com/maps/api/elevation/json?locations=50.01,10.2|51.01,11.2
     */
    public function testSimpleResponse()
    {
        $google = new GoogleMaps($this->getMockForResponses([
            new Response(200, [], '{"results":[{"elevation":203,"location":{"lat":50.01,"lng":10.2},"resolution":9.543951988220215},{"elevation":240.9113922119141,"location":{"lat":51.01,"lng":11.2},"resolution":19.08790397644043}],"status":"OK"}')
        ]));
        $google->setPointsToGroup(1);

        $this->assertEquals([203, 241], $google->loadAltitudeData([50.01, 51.01], [10.2, 11.2]));
    }

    /**
     * @see http://maps.googleapis.com/maps/api/elevation/json?locations=49.445,7.765|0.0,0.0|49.450,7.770
     */
    public function testGuessingUnknown()
    {
        $google = new GoogleMaps($this->getMockForResponses([
            new Response(200, [], '{ "results" : [ { "elevation" : 237.1988372802734, "location" : { "lat" : 49.445, "lng" : 7.765 }, "resolution" : 152.7032318115234 }, { "elevation" : -3492, "location" : { "lat" : 0, "lng" : 0 }, "resolution" : 610.8129272460938 }, { "elevation" : 263.6353454589844, "location" : { "lat" : 49.45, "lng" : 7.77 }, "resolution" : 152.7032318115234 } ], "status" : "OK" }')
        ]));
        $google->setPointsToGroup(1);

        $this->assertEquals([237, 237, 264], $google->loadAltitudeData([49.445, 0, 49.450], [7.765, 0, 7.770]));
    }
}
